<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingPage\PageSettingResource;
use App\Services\PageSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
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

    
    public function getPageSettings(string $page): JsonResponse
    {
        $setting = $this->settingService
            ->getOneSettingsByPage($page);

        return response()->json(
            $setting ? new PageSettingResource($setting) : null,
            JsonResponse::HTTP_OK
        );
    }

}
