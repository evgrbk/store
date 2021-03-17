<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\AutoImport\AutoImportIndexRequest;
use App\Http\Requests\AdminPanel\AutoImport\AutoImportStoreRequest;
use App\Http\Requests\AdminPanel\AutoImport\AutoImportUpdateRequest;
use App\Http\Requests\AdminPanel\AutoImport\AutoImportPrepareRequest;
use App\Http\Resources\AdminPanel\AutoImportResourceCollection;
use App\Http\Resources\AdminPanel\AutoImportResource;
use App\Http\Resources\AdminPanel\AutoImportLogResource;
use App\Services\AutoImportService;
use Illuminate\Http\JsonResponse;
use App\Models\AutoImport;
use App\Models\AutoImportLog;

class AutoImportController extends Controller
{
    /**
     * @var AutoImportService
     */
    private AutoImportService $autoImportService;

    /**
     * AutoImportController constructor.
     *
     * @param AutoImportService $regionService
     */
    public function __construct(AutoImportService $autoImportService)
    {
        $this->autoImportService = $autoImportService;
    }

    /**
     * Show auto imports
     *
     * @param AutoImportIndexRequest $request
     * @return JsonResponse
     */
    public function index(AutoImportIndexRequest $request): JsonResponse
    {
        $this->checkPermission('read-auto-imports');

        $data = $this->autoImportService
            ->getList($request->limit);

        return response()
            ->json(AutoImportResourceCollection::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Store auto import
     *
     * @param AutoImportStoreRequest $request
     * @return JsonResponse
     */
    public function store(AutoImportStoreRequest $request): JsonResponse
    {
        $this->checkPermission('create-auto-imports');

        $data = $this->autoImportService
            ->store($request->validated());

        return response()
            ->json(AutoImportResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Update auto import
     *
     * @param AutoImportUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AutoImportUpdateRequest $request, $id): JsonResponse
    {
        $this->checkPermission('update-auto-imports');

        if (AutoImport::find($id)->isInProgress()) {
            return response()
                ->json([
                    'message' => 'Нельзя изменить, т.к. выполняется импорт'
                ], 422);
        }

        $data = $this->autoImportService
            ->update($request->validated(), $id);

        return response()
            ->json(AutoImportResource::make($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete auto import
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->checkPermission('delete-auto-imports');

        if (AutoImport::find($id)->isInProgress()) {
            return response()
                ->json([
                    'message' => 'Нельзя удалить, т.к. выполняется импорт'
                ], 422);
        }

        $this->autoImportService
            ->delete($id);

        return response()
            ->json([], JsonResponse::HTTP_OK);
    }

    /**
     * Prepare url for import
     *
     * @param AutoImportPrepareRequest $request
     * @return JsonResponse
     */
    public function prepare(AutoImportPrepareRequest $request): JsonResponse
    {
        $this->checkPermission('create-auto-imports');

        return $this->autoImportService
            ->prepareUrl($request->url);

    }

    /**
     * Display logs of auto import
     *
     * @param int $id
     * @return JsonResponse
     */
    public function showLogs(int $id): JsonResponse
    {
        $this->checkPermission('read-auto-imports');

        $logs = AutoImportLog::where('auto_import_id', $id)->orderByDesc('id')->get();
        return response()
            ->json(AutoImportLogResource::collection($logs), JsonResponse::HTTP_OK);
    }
}
