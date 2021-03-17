<?php

namespace App\Services;

use App\Repositories\RegionRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RegionService extends Service
{
    /**
     * @var RegionRepository
     */
    private $regionRepository;

    /**
     * RegionService constructor.
     *
     * @param RegionRepository $regionRepository
     */
    public function __construct(RegionRepository $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    /**
     * Get list of regions
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $query = $this->regionRepository
            ->withCountry();

        return $this->regionRepository
            ->getPaginate($query, $limit);
    }

    /**
     * Create region
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->regionRepository
            ->store($data);
    }

    /**
     * Update region
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->regionRepository
            ->update($data, $id);
    }

    /**
     * Delete region
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->regionRepository
            ->destroy($id);
    }

    /**
     * Get all names
     *
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->regionRepository
            ->allColumns(['id', 'country_id', 'name']);
    }
}
