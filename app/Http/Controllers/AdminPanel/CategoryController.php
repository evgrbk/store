<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\IndexRequest;
use App\Http\Requests\AdminPanel\StoreCategoriesRequest;
use App\Http\Requests\AdminPanel\UpdateCategoriesRequest;
use App\Http\Resources\AdminPanel\CategoryResource;
use App\Http\Resources\CategoryWithSubcategoriesResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

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
     * get list of categories
     *
     * @param IndexRequest $request
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $data = $this
            ->categoryService
            ->getCategoriesList($limit);

        return response()
            ->json([
                'data' => CategoryResource::collection($data),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    /**
     * to create new category
     *
     * @param StoreCategoriesRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoriesRequest $request): JsonResponse
    {
        $this->checkPermission('create-categories');

        $data = $request->validated();

        $data = $this->categoryService
            ->storeCategories($data);

        return response()
            ->json(['data' => CategoryResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * update category
     *
     * @param StoreCategoriesRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateCategoriesRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-categories');

        $data = $request->validated();

        $data = $this->categoryService
            ->updateCategories($data, $id);

        return response()
            ->json(['data' => CategoryResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * remove category
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-categories');

        if ($this->categoryService->deleteCategory($id)) {
            return response()->json([], JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Невозможно удалить категорию, т.к. у нее есть товары!',
            ], 422);
        }
    }

    /**
     * All categories names
     *
     * @return JsonResponse
     */
    public function allNames(): JsonResponse
    {
        $this->checkPermission('read-categories');

        $data = $this->categoryService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }


}
