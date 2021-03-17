<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Setting\SettingIndexRequest;
use App\Http\Requests\AdminPanel\Setting\SettingStoreRequest;
use App\Http\Requests\AdminPanel\Setting\SettingUpdateRequest;
use App\Http\Resources\AdminPanel\SettingResource;
use App\Http\Resources\AdminPanel\SettingResourceCollection;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * @var SettingService
     */
    private SettingService $settingService;

    /**
     * SettingController constructor.
     *
     * @param SettingService $currencyService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Show settings
     *
     * @param SettingIndexRequest $request
     * @return JsonResponse
     */
    public function index(SettingIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-settings');

        $data = $this->settingService
            ->getList($request->limit);

        return response()
            ->json(SettingResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store setting
     *
     * @param SettingStoreRequest $request
     * @return JsonResponse
     */
    public function store(SettingStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-settings');

        $data = $this->settingService
            ->store($request->validated());

        return response()
            ->json(SettingResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Show setting
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $this->checkPermission('read-settings');

        $data = $this->settingService
            ->getOne($id);

        return response()
            ->json(new SettingResource($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update setting
     *
     * @param SettingUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SettingUpdateRequest $request, int $id): JsonResponse
    {
        $this->checkPermission('update-settings');

        $data = $this->settingService
            ->update($request->validated(), $id);

        return response()
            ->json(SettingResource::make($data), JsonResponse::HTTP_OK);
    }

}
