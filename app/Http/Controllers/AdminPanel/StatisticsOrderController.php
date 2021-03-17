<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Statistic\StatisticOrders;
use App\Http\Resources\AdminPanel\StatisticsOrderResource;
use App\Services\StatisticsOrderService;
use Illuminate\Http\JsonResponse;

class StatisticsOrderController extends Controller
{
    /**
     * @var StatisticsOrderService
     */
    public StatisticsOrderService $orderService;

    /**
     * StatisticsOrderController constructor.
     *
     * @param StatisticsOrderService $orderService
     */
    public function __construct(
        StatisticsOrderService $orderService
    ) {
        $this->orderService = $orderService;
    }

    /**
     * Get all statuses for front
     *
     * @return JsonResponse
     */
    public function getOrderStatus(): JsonResponse
    {
        $statuses = $this
            ->orderService
            ->getAllOrderStatuses();

        return response()->json($statuses, JsonResponse::HTTP_OK);
    }

    /**
     * Get all orders
     *
     * @param StatisticOrders $statisticOrders
     * @return JsonResponse
     */
    public function getAllOrders(StatisticOrders $statisticOrders): JsonResponse
    {
        $params = $statisticOrders->all();

        $orders = $this
            ->orderService
            ->getAllOrdersWith($params);

        return response()->json([
            'data' => StatisticsOrderResource::collection($orders),
            'count' => $orders->total(),
        ],
            
            JsonResponse::HTTP_OK
        );
    }
}
