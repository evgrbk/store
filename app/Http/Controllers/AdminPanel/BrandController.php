<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Http\Resources\AdminPanel\BrandResource;
use App\Http\Requests\AdminPanel\Brand\IndexBrandRequest;
use App\Http\Requests\AdminPanel\Brand\CreateBrandRequest;
use App\Http\Requests\AdminPanel\Brand\UpdateBrandRequest;
use App\Http\Requests\AdminPanel\Brand\FindBrandRequest;
use Illuminate\Http\JsonResponse;
use Auth;

class BrandController extends Controller
{
    /**
     * @var BrandService
     */
    protected $brandService;

    /**
     * BrandController constructor.
     *
     * @param BrandService $service
     */
    public function __construct(BrandService $service)
    {
        $this->brandService = $service;
    }

    /**
     * Get all brands
     *
     * @param IndexBrandRequest $request
     * @return JsonResponse
     */
    public function index(IndexBrandRequest $request): JsonResponse
    {
        $this->checkPermission('read-brands');

        $allBrands = $this->brandService
            ->getList($request->limit);

        return response()->json([
            'data' => BrandResource::collection($allBrands),
            'count' => $allBrands->total(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Show brand
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $this->checkPermission('read-brands');

        $data = $this->brandService
            ->getOne($id);

        return response()
            ->json(new BrandResource($data)
                , JsonResponse::HTTP_OK);
    }

    /**
     * Create brand
     *
     * @param CreateBrandRequest $request
     * @return JsonResponse
     */
    public function store(CreateBrandRequest $request): JsonResponse
    {
        $this->checkPermission('create-brands');

        $data = $this->brandService
            ->createOrUpdate($request->all());

        return response()->json([
            'data' => new BrandResource($data),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update brand
     *
     * @param UpdateBrandRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request, $id): JsonResponse
    {
        if (!Auth::user()->hasPermission('update-brands')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->brandService
            ->createOrUpdate($request->validated(), $id);

        return response()->json(new BrandResource($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete brand
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->checkPermission('delete-brands');

        if ($this->brandService->destroy($id)) {
            return response()->json([], JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Невозможно удалить бренд, т.к. у него есть товары!',
            ], 422);
        }
    }

    /**
     * Find user
     *
     * @param FindBrandRequest $request
     * @return JsonResponse
     */
    public function findBrands(FindBrandRequest $request): JsonResponse
    {
        $this->checkPermission('read-brands');

        $data = $this->brandService
            ->findBrand($request->validated());

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * All brand names
     *
     * @return JsonResponse
     */
    public function allNames(): JsonResponse
    {
        $this->checkPermission('read-brands');

        $data = $this->brandService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }
}
