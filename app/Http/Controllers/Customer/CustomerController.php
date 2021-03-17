<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerIndexRequest;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Http\Requests\Customer\CustomerAuthRequest;
use App\Http\Resources\Customer\CustomerResourceCollection;
use App\Http\Resources\Customer\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * @var CustomerService
     */
    private CustomerService $customerService;

    /**
     * CustomerController constructor.
     *
     * @param CustomerService $customerService
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Show customer
     *
     * @param CustomerIndexRequest $request
     * @return JsonResponse
     */
    public function index(CustomerIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-customers');

        $data = $this->customerService
            ->getList($request->limit, $request->except('limit'));

        return response()
            ->json(CustomerResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Get customer
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if (auth()->guard('customers')->id() != $id)
            $this->checkPermission('read-customers');

        $data = $this->customerService
            ->getOne($id);

        return response()
            ->json(CustomerResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store customer
     *
     * @param CustomerStoreRequest $request
     * @return JsonResponse
     */
    public function store(CustomerStoreRequest $request): JsonResponse
    {
        $data = $this->customerService
            ->create($request->validated());

        return response()
            ->json(CustomerResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update customer
     *
     * @param CustomerUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(CustomerUpdateRequest $request, int $id): JsonResponse
    {
        if (auth()->guard('customers')->id() != $id)
            $this->checkPermission('update-customers');

        $data = $this->customerService
            ->update($request->validated(), $id);

        return response()
            ->json(CustomerResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete customer
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->checkPermission('delete-customers');

        $this->customerService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }


    /**
     * Auth customer
     *
     * @param CustomerAuthRequest $request
     * @return Authenticatable
     */
    public function auth(CustomerAuthRequest $request): JsonResponse
    {
        return $this->customerService
            ->auth($request->validated());
    }

    /**
     * Get customer
     *
     * @return Authenticatable
     */
    public function me(): Authenticatable
    {
        return auth()->guard('customers')->user();
    }
}
