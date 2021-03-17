<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderIndexRequest;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Http\Resources\Order\OrderResourceCollection;
use App\Http\Resources\Order\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    private OrderService $orderService;

    /**
     * OrderController constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show order
     *
     * @param OrderIndexRequest $request
     * @return JsonResponse
     */
    public function index(OrderIndexRequest $request): JsonResponse
    {
        if (!$customer_id = auth()->guard('customers')->id()) {
            $this->checkPermission('read-orders');
        }

        $data = $this->orderService
            ->getList($request->limit ?? 10, $customer_id);

        return response()
            ->json(OrderResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Get order
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if (!$customer_id = auth()->guard('customers')->id()) {
            $this->checkPermission('read-orders');
        }

        $data = $this->orderService
            ->showOne($id, $customer_id);

        return response()
            ->json($data ? OrderResource::make($data) : [], JsonResponse::HTTP_OK);
    }

    /**
     * Store orders
     *
     * @param OrderStoreRequest $request
     * @return JsonResponse
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        if ($order = $this->orderService->create($request->validated())) {
            return response()
                ->json(OrderResource::make($order), JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании заказа!',
            ], 422);
        }
    }

    /**
     * Update order
     *
     * @param OrderUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(OrderUpdateRequest $request, int $id): JsonResponse
    {
        $this->checkPermission('edit-orders');

        $data = $this->orderService
            ->update($request->validated(), $id);

        return response()
            ->json(OrderResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete order
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->checkPermission('delete-orders');

        $this->orderService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }
}
