<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Discount\DiscountIndexRequest;
use App\Http\Requests\AdminPanel\Discount\DiscountStoreRequest;
use App\Http\Requests\AdminPanel\Discount\DiscountUpdateRequest;
use App\Http\Resources\AdminPanel\DiscountResource;
use App\Http\Resources\AdminPanel\DiscountResourceCollection;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    /**
     * @var DiscountService
     */
    private DiscountService $discountService;

    /**
     * DiscountController constructor.
     *
     * @param DiscountService $discountService
     */
    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Show discounts
     *
     * @param DiscountIndexRequest $request
     * @return JsonResponse
     */
    public function index(DiscountIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-discounts');

        $data = $this->discountService
            ->getList($request->limit);

        return response()
            ->json(DiscountResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store discount
     *
     * @param DiscountStoreRequest $request
     * @return JsonResponse
     */
    public function store(DiscountStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-discounts');

        $data = $this->discountService
            ->storeModel($request->validated());

        if ($data) {
            return response()
                ->json(DiscountResource::make($data), JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании!',
            ], 422);
        }
    }

    /**
     * Update discount
     *
     * @param DiscountUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(DiscountUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-discounts');

        $data = $this->discountService
            ->updateModel($request->validated(), $id);

        if ($data) {
            return response()
                ->json(DiscountResource::make($data), JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении!',
            ], 422);
        }
    }

    /**
     * Delete discount
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-discounts');

        $this->discountService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }

}
