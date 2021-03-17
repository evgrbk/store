<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\CheckUniqueGoodRequest;
use App\Http\Requests\AdminPanel\Good\ImportGoodRequest;
use App\Http\Requests\AdminPanel\Good\PrepareImportGoodRequest;
use App\Http\Requests\AdminPanel\IndexRequest;
use App\Http\Requests\AdminPanel\StoreGoodRequest;
use App\Http\Requests\AdminPanel\UpdateGoodRequest;
use App\Http\Requests\AdminPanel\Good\ExportGoodRequest;
use App\Http\Resources\AdminPanel\GoodResource;
use App\Http\Requests\AdminPanel\Good\TranslateGoodRequest;
use App\Http\Requests\AdminPanel\Good\TranslateGoodUpdateRequest;
use App\Http\Resources\AdminPanel\GoodTranslateResource;
use App\Services\GoodService;
use App\Services\GoodsImport\ParserService;
use Illuminate\Http\JsonResponse;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GoodController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $data = $this->goodService
            ->getList($limit, $request->except('limit'));

        return response()
            ->json([
                'data' => GoodResource::collection($data),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreGoodRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreGoodRequest $request): JsonResponse
    {
        $this->checkPermission('create-goods');

        $data = $request->all();

        $data = $this->goodService
            ->store($data);

        return response()
            ->json(['data' => GoodResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->goodService
            ->getGood($id);

        return response()
            ->json(['data' => GoodResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGoodRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(UpdateGoodRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-goods');

        $data = $request->all();

        $data = $this->goodService
            ->update($data, $id);

        return response()
            ->json(['data' => GoodResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-goods');

        $data = $this->goodService
            ->delete($id);

        return response()
            ->json(['data' => $data], JsonResponse::HTTP_OK);
    }

    /**
     * Check good seo h1 and seo title for uniqueness
     *
     * @param CheckUniqueGoodRequest $request
     * @return JsonResponse
     */
    public function checkUnique(CheckUniqueGoodRequest $request): JsonResponse
    {
        return response()
            ->json([
                'data' => [
                    'warning' => false
                ]
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Prepare goods file to import
     *
     * @param PrepareImportGoodRequest $request
     * @return JsonResponse
     */
    public function prepareImport(PrepareImportGoodRequest $request): JsonResponse
    {
        $this->checkPermission('import-goods');

        $serviceResponse = app(ParserService::class)
            ->prepare($request);

        return $serviceResponse;
    }

    /**
     * Import file to db
     *
     * @param ImportGoodRequest $request
     * @return JsonResponse
     */
    public function import(ImportGoodRequest $request): JsonResponse
    {
        $this->checkPermission('import-goods');

        $serviceResponse = app(ParserService::class)
            ->import($request);

        return $serviceResponse;
    }

    /**
     * Export goods
     *
     * @param ExportGoodRequest $request
     * @return BinaryFileResponse
     */
    public function export(ExportGoodRequest $request): BinaryFileResponse
    {
        $this->checkPermission('export-goods');

        if ($request->fileFormat == 'excel') {
            return $this->goodService
                ->exportExcel($request->except('fileFormat'));
        }
    }

    /**
     * Translate goods
     *
     * @param TranslateGoodRequest $request
     * @return JsonResponse
     */
    public function translate(TranslateGoodRequest $request): JsonResponse
    {
        $this->checkPermission('translate-goods');

        return $this->goodService
            ->translate($request->validated());
    }

    /**
     * Get translates by good id
     *
     * @param int $id
     * @return JsonResponse
     */
    public function goodTranslates(int $id): JsonResponse
    {
        $this->checkPermission('translate-goods');

        $translates = $this->goodService
            ->goodTranslates($id);

        return response()->json(GoodTranslateResource::collection($translates), JsonResponse::HTTP_OK);
    }

    /**
     * Update one translate
     *
     * @param TranslateGoodUpdateRequest $request
     * @return JsonResponse
     */
    public function updateTranslation(TranslateGoodUpdateRequest $request): JsonResponse
    {
        $this->checkPermission('translate-goods');

        $translation = $this->goodService
            ->translateUpdate($request->validated());

        if($translation) {
            return response()->json(GoodTranslateResource::make($translation), JsonResponse::HTTP_OK);
        } else {
            return response()->json([], JsonResponse::HTTP_OK);
        }
    }
}
