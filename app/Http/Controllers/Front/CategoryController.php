<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryWithSubcategoriesResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * CategoryController constructor.
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * get categories parent - children
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this
            ->categoryService
            ->getCategoriesListWithRelation();

        return response()
            ->json([
                'data' => CategoryWithSubcategoriesResource::collection($data),
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $data = $this->categoryService
            ->getCategory($id);

        return response()
            ->json(['data' => CategoryWithSubcategoriesResource::make($data)], JsonResponse::HTTP_OK);
    }

    public function showBySlug($slug): JsonResponse
    {
        $data = $this->categoryService
            ->getCategoryBySlug($slug);

        return response()
            ->json(['data' => CategoryWithSubcategoriesResource::make($data)], JsonResponse::HTTP_OK);
    }
}
