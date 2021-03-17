<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SettingService extends Service
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * SettingService constructor.
     *
     * @param SettingRepository $settingRepository
     */
    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Get list of settings
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        return $this->settingRepository
            ->paginateAll($limit);
    }

    /**
     * Create setting
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->settingRepository
            ->store($data);
    }

    /**
     * Show setting by id or name
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        return $this->settingRepository
            ->getRecord($id);
    }

    /**
     * Update setting
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->settingRepository->update($data, $id);
    }
}
