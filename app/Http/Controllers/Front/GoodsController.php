<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Good\GoodRatingStoreRequest;
use App\Http\Resources\GoodResourceGeneral;
use App\Models\Category;
use App\Services\GoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    /**
     * @var GoodService
     */
    private GoodService $goodService;

    /**
     * GoodController constructor.
     * @param GoodService $goodService
     */
    public function __construct(GoodService $goodService)
    {
        $this->goodService = $goodService;
    }

    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit') ? $request->get('limit') : 9;

        $category = Category::query()
            ->where('seo_slug', $request->get('category'))
            ->first();

        $request->merge(['category_id' => $category->id]);

        $data = $this->goodService
            ->getListWithFilter($limit, $request->except('limit'));

        return response()
            ->json([
                'data' => GoodResourceGeneral::collection($data),
                'count' => $data->total(),
                'pages' => $data->lastPage(),
                'category' => $category
            ], JsonResponse::HTTP_OK);
    }


    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show($slug): JsonResponse
    {
        $data = $this->goodService
            ->getGoodBySlug($slug);

        return response()
            ->json(['data' => GoodResourceGeneral::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request)
    {
        $limit = $request->get('limit') ? $request->get('limit') : 20;
        $goods = $this->goodService
            ->getListWithFilter($limit, $request->except('limit'));

        return response()->json([
            'data' => GoodResourceGeneral::collection($goods->items()),
            'count' => $goods->total()
        ]);
    }

    /**
     * Set good rating
     *
     * @param GoodRatingStoreRequest $request
     * @return JsonResponse
     */
    public function rating(GoodRatingStoreRequest $request): JsonResponse
    {
        $rating = $this->goodService
            ->setRating($request->validated());

        return response()->json([
            'status' => 'ok',
            'rating' => $rating
        ], JsonResponse::HTTP_OK);
    }
}
