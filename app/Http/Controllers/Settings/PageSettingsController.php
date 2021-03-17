<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\CreateSettingRequest;
use App\Http\Requests\Setting\IndexSettingRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Resources\SettingPage\PageSettingResource;
use App\Services\PageSettingService;
use Illuminate\Http\JsonResponse;

class PageSettingsController extends Controller
{
    /**
     * @var PageSettingService
     */
    protected PageSettingService $settingService;

    /**
     * PageSettingsController constructor.
     *
     * @param PageSettingService $service
     */
    public function __construct(PageSettingService $service)
    {
        $this->settingService = $service;
    }

    /**
     * Get all settings
     *
     * @param IndexSettingRequest $request
     * @return JsonResponse
     */
    public function index(IndexSettingRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $settings = $this->settingService
            ->getAllSettings($limit);

        return response()->json([
            'data' => PageSettingResource::collection($settings),
            'count' => $settings->total(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Create setting
     *
     * @param CreateSettingRequest $request
     * @return JsonResponse
     */
    public function store(CreateSettingRequest $request): JsonResponse
    {
        $data = $request->all();
        $files = $request->file();
        $model = $this->settingService
                      ->createSetting($data, $files);

        return response()->json([
            'data' => new PageSettingResource($model)
            ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $setting = $this->settingService
            ->getOneSettingsById($id);

        return response()->json(
            new PageSettingResource($setting),
            JsonResponse::HTTP_OK
        );
    }

    /**
     * Update one setting record.
     *
     * @param UpdateSettingRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(UpdateSettingRequest $request, $id): JsonResponse
    {
        $data = $this->settingService
            ->updateSetting($request->validated(), $id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * Delete one setting
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(int $id): JsonResponse
    {
        $data = $this->settingService
            ->destroyOneSetting($id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * To get settings for a page by page title
     *
     * @param string $page
     * @return JsonResponse
     */
    public function getPageSettings(string $page): JsonResponse
    {
        $setting = $this->settingService
            ->getOneSettingsByPage($page);

        return response()->json(
            $setting ? new PageSettingResource($setting) : null,
            JsonResponse::HTTP_OK
        );
    }

    /**
     * To update page settings for a page by page title
     *
     * @param UpdateSettingRequest $request
     * @param string $page
     * @return JsonResponse
     * @throws \Exception
     */
    public function setPageSettings(UpdateSettingRequest $request, string $page): JsonResponse
    {
        $data = $this->settingService
            ->updateSettingByPage($request->validated(), $page);

        return response()->json(new PageSettingResource($data), JsonResponse::HTTP_OK);
    }
}
