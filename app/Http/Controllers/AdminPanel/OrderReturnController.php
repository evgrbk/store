<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Order\OrderReturnIndexRequest;
use App\Http\Requests\AdminPanel\Order\OrderReturnUpdateRequest;
use App\Http\Resources\AdminPanel\OrderReturnResource;
use App\Http\Resources\AdminPanel\OrderReturnResourceCollection;
use App\Services\OrderReturnService;
use Illuminate\Http\JsonResponse;

class OrderReturnController extends Controller
{
    /**
     * @var OrderReturnService
     */
    private OrderReturnService $orderReturnService;

    /**
     * OrderReturnController constructor.
     *
     * @param OrderReturnService $orderReturnService
     */
    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;
    }

    /**
     * Show all order returns
     *
     * @param OrderReturnIndexRequest $request
     * @return JsonResponse
     */
    public function index(OrderReturnIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-order-returns');

        $data = $this->orderReturnService
            ->getList($request->limit, $request->except('limit'));

        return response()
            ->json(OrderReturnResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Get order return
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $this->checkPermission('read-order-returns');

        $data = $this->orderReturnService
            ->getOne($id);

        return response()
            ->json(OrderReturnResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update order return
     *
     * @param OrderReturnUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(OrderReturnUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-order-returns');

        $data = $this->orderReturnService
            ->update($request->validated(), $id);

        return response()
            ->json(OrderReturnResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete order return
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-order-returns');

        $this->orderReturnService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }
}
