<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Language\LanguageIndexRequest;
use App\Http\Requests\AdminPanel\Language\LanguageStoreRequest;
use App\Http\Requests\AdminPanel\Language\LanguageUpdateRequest;
use App\Http\Resources\AdminPanel\LanguageResource;
use App\Http\Resources\AdminPanel\LanguageResourceCollection;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;

class LanguagesController extends Controller
{
    /**
     * @var LanguageService
     */
    private LanguageService $languageService;

    /**
     * LanguageController constructor.
     *
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Show languages
     *
     * @param LanguageIndexRequest $request
     * @return JsonResponse
     */
    public function index(LanguageIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-languages');

        $data = $this->languageService
            ->getList($request->limit);

        return response()
            ->json(LanguageResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store language
     *
     * @param LanguageStoreRequest $request
     * @return JsonResponse
     */
    public function store(LanguageStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-languages');

        $data = $this->languageService
            ->store($request->validated());

        return response()
            ->json(LanguageResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update language
     *
     * @param LanguageUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(LanguageUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-languages');

        $data = $this->languageService
            ->update($request->validated(), $id);

        return response()
            ->json(LanguageResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete language
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-languages');

        if ($this->languageService->delete($id)) {
            return response()
                ->json([], JsonResponse::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалсь удалить этот язык!',
            ], 422);
        }

    }

    /**
     * All names
     *
     * @return JsonResponse
     */
    public function allNames(): JsonResponse
    {
        $this->checkPermission('read-languages');

        $data = $this->languageService
            ->getNames();

        return response()
            ->json($data, JsonResponse::HTTP_OK);
    }
}
