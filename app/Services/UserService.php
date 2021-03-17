<?php

namespace App\Services;

use App\Mail\UserChangedData;
use App\Mail\UserLoginPass;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Lang;

class UserService extends Service
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository $repository
     * @param RoleRepository $roleRepository
     */
    public function __construct(UserRepository $repository, RoleRepository $roleRepository)
    {
        $this->userRepository = $repository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param int $limit
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getList(int $limit, array $params = []): LengthAwarePaginator
    {
        $users = $this->userRepository
            ->paginateAll($limit, $params);

        return $users;
    }

    /**
     * Get user
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        $model = $this->userRepository
            ->getRecord($id);

        return $model;
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $model = $this->userRepository
            ->store($data);

        if (isset($data['role_id'])) {
            $role = $this->roleRepository->getRecord($data['role_id']);
            $model->attachRole($role);
        }

        try {
            Mail::to($model)->send(new UserLoginPass([
                'name' => $data['name'],
                'url' => config('mail.admin_url'),
                'login' => $data['email'],
                'pass' => $data['password']
            ]));
        } catch (\Exception $e) {
            report($e);
        }

        return $model;
    }

    /**
     * Update user
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function update(array $data, int $id): Model
    {
        try {
            DB::beginTransaction();

            $model = $this
                ->userRepository
                ->update($data, $id);

            if (isset($data['role_id'])) {
                $model->detachRoles($model->roles);
                $role = $this->roleRepository->getRecord($data['role_id']);
                $model->attachRole($role);
                $model->role_id = $role->id;
            } else {
                $role = $model->roles->first();
                if ($role) {
                    $model->role_id = $role->id;
                }
            }

            DB::commit();

            if ($changes = $model->getChanges()) {
                unset($changes['updated_at']);
                foreach ($changes as $changeField => $value) {
                    if ($changeField == 'password') {
                        $value = $data['password'];
                    } else if ($changeField == 'date_of_birth') {
                        $value = $model->date_of_birth->format('d.m.Y');
                    } else if ($changeField == 'gender') {
                        $value = $value == 'male' ? 'мужской' : 'женский';
                    }

                    if (Lang::has('validation.attributes.' . $changeField)) {
                        $changes[Lang::get('validation.attributes.' . $changeField)] = $value;
                        unset($changes[$changeField]);
                    }
                }
                try {
                    Mail::to($model)->send(new UserChangedData($model->name, $changes));
                } catch (\Exception $e) {
                    report($e);
                }
            }

            return $model;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function destroy(int $id): bool
    {
        return $this->userRepository
            ->destroy($id);
    }

    /**
     * Get all roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function allRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->roleRepository
            ->all();
    }

}
