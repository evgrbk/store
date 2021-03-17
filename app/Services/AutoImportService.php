<?php

namespace App\Services;

use App\Http\Requests\AdminPanel\Good\PrepareImportGoodRequest;
use App\Repositories\AutoImportRepository;
use App\Services\GoodsImport\ParserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use App\Models\AutoImport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Throwable;

class AutoImportService extends Service
{
    /**
     * @var AutoImportRepository
     */
    private $autoImportRepository;

    /**
     * @var ParserService
     */
    private ParserService $parserService;

    /**
     * AutoImportService constructor.
     *
     * @param AutoImportRepository $autoImportRepository
     * @param ParserService $parserService
     */
    public function __construct(AutoImportRepository $autoImportRepository,
                                ParserService $parserService)
    {
        $this->autoImportRepository = $autoImportRepository;
        $this->parserService = $parserService;
    }

    /**
     * Get list of auto imports
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        return $this->autoImportRepository
            ->paginateAll($limit);
    }

    /**
     * Create auto import
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $data['status'] = AutoImport::STATUS_PENDING;

        return $this->autoImportRepository
            ->store($data);
    }

    /**
     * Update auto import
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->autoImportRepository
            ->update($data, $id);
    }

    /**
     * Delete auto import
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->autoImportRepository
            ->destroy($id);
    }

    /**
     * Get fields for import
     *
     * @param string $url
     * @return JsonResponse
     */
    public function prepareUrl(string $url): JsonResponse
    {
        try {
            $file = Http::retry(3, 1000)
                ->timeout(20)
                ->get($url)
                ->throw()
                ->body();
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при скачивании файла!',
            ], 422);
        }

        return $this->parserService
            ->prepare(new PrepareImportGoodRequest, $file);
    }
}
