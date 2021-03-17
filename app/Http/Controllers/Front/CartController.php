<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\IndexCartRequest;
use App\Http\Requests\Cart\StoreCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Requests\Cart\DeleteCartRequest;
use App\Services\CartService;
use App\Http\Resources\Cart\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * CartController constructor.
     *
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart
     *
     * @param IndexCartRequest $request
     * @return JsonResponse
     */
    public function index(IndexCartRequest $request): JsonResponse
    {
        $response = $this
            ->cartService
            ->getAll($request->validated());

        return response()->json(new CartResource($response), JsonResponse::HTTP_OK);
    }

    /**
     * Add good to cart
     *
     * @param StoreCartRequest $request
     * @return JsonResponse
     */
    public function store(StoreCartRequest $request): JsonResponse
    {
        $response = $this
            ->cartService
            ->addGood($request->validated());

        return response()->json(new CartResource($response), JsonResponse::HTTP_OK);
    }

    /**
     * Update count of good in cart
     *
     * @param UpdateCartRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCartRequest $request, int $id): JsonResponse
    {
        $response = $this
            ->cartService
            ->updateGood($request->validated(), $id);

        return response()->json(new CartResource($response), JsonResponse::HTTP_OK);
    }

    /**
     * Delete good from cart
     *
     * @param DeleteCartRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DeleteCartRequest $request, int $id): JsonResponse
    {
        $response = $this
            ->cartService
            ->deleteGood($request->validated(), $id);

        return response()->json(new CartResource($response), JsonResponse::HTTP_OK);
    }
}
