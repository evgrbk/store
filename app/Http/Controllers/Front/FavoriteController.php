<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\FavoriteIndexRequest;
use App\Http\Requests\Favorite\FavoriteStoreRequest;
use App\Http\Resources\GoodResourceGeneral;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * @var FavoriteService
     */
    protected $favoriteService;

    /**
     * Favorite constructor.
     *
     * @param FavoriteService $favoriteService
     */
    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Get favorites of customer
     *
     * @param FavoriteIndexRequest $request
     * @return JsonResponse
     */
    public function index(FavoriteIndexRequest $request): JsonResponse
    {
        $favoriteGoods = $this->favoriteService->getCustomerFavorites($request->validated());

        return response()->json($favoriteGoods, JsonResponse::HTTP_OK);
    }

    /**
     * Add/delete from favorite
     *
     * @param FavoriteStoreRequest $request
     * @return JsonResponse
     */
    public function store(FavoriteStoreRequest $request): JsonResponse
    {
        $isFavorite = $this->favoriteService->createOrDelete($request->validated());

        return response()->json(['is_favorite' => $isFavorite], JsonResponse::HTTP_OK);
    }
}
