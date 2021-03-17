<?php

namespace App\Services;

use App\Mail\UserChangedData;
use App\Mail\UserLoginPass;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class CustomerService extends Service
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * CustomerService constructor.
     *
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->customerRepository = $repository;
    }

    /**
     * Get customers
     *
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getList(int $limit, array $params = []): LengthAwarePaginator
    {
        return $this->customerRepository
            ->paginateAllWithFilters($limit, $params);
    }

    /**
     * Get customer
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        return $this->customerRepository
            ->getRecord($id);
    }

    /**
     * Create new customer
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $customer = $this->customerRepository
            ->store($data);

        $customer->attachRole('user');

        try {
            Mail::to($customer)->send(new UserLoginPass([
                'name' => $data['full_name'],
                'url' => config('mail.front_url'),
                'login' => $data['email'] . ' или ' . $data['phone'],
                'pass' => $data['password']
            ]));
        } catch (\Exception $e) {
            report($e);
        }
        return $customer;
    }

    /**
     * Update customer
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function update(array $data, int $id): Model
    {
        $model = $this
            ->customerRepository
            ->update($data, $id);

        if ($changes = $model->getChanges()) {
            unset($changes['updated_at']);
            foreach ($changes as $changeField => $value) {
                if ($changeField == 'password') {
                    $value = $data['password'];
                } else if ($changeField == 'dob') {
                    $value = $model->dob->format('d.m.Y');
                } else if ($changeField == 'is_male') {
                    $value = $value ? 'мужской' : 'женский';
                } else if ($changeField == 'is_active') {
                    $value = $value ? 'да' : 'нет';
                }

                if (Lang::has('validation.attributes.' . $changeField)) {
                    $changes[Lang::get('validation.attributes.' . $changeField)] = $value;
                    unset($changes[$changeField]);
                }
            }
            try {
                Mail::to($model)->send(new UserChangedData($model->full_name, $changes));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return $model;

    }

    /**
     * Delete customer
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        return $this->customerRepository
            ->destroy($id);
    }

    /**
     * Auth user
     *
     * @param array $data
     * @return JsonResponse
     */
    public function auth(array $data): JsonResponse
    {
        $credentials = [
            'password' => $data['password']
        ];

        $credentials[filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone'] = $data['login'];

        if (auth()->guard('customers')->attempt($credentials)) {
            return response()->json([], 204);
        } else {
            return response()->json([], 422);
        }
    }

}
