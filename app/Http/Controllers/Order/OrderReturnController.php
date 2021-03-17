<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderReturnStoreRequest;
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
     * Store order return
     *
     * @param OrderReturnStoreRequest $request
     * @return JsonResponse
     */
    public function store(OrderReturnStoreRequest $request): JsonResponse
    {
        $data = $this->orderReturnService
            ->store($request->validated());

        return response()->json([
            'status' => 'ok',
            'id' => $data->id
        ], JsonResponse::HTTP_OK);
    }
}
