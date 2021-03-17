<?php

namespace App\Services;

use App\Repositories\CountryRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CountryService extends Service
{
    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * CountryService constructor.
     *
     * @param CountryRepository $countryRepository
     */
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * Get list of countries
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $query = $this->countryRepository
            ->withRelations();

        return $this->countryRepository
            ->getPaginate($query, $limit);
    }

    /**
     * Create country
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->countryRepository
            ->store($data);
    }

    /**
     * Update country
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->countryRepository
            ->update($data, $id);
    }

    /**
     * Delete country
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->countryRepository
            ->destroy($id);
    }

    /**
     * Get all names
     *
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->countryRepository
            ->allColumns(['id', 'name']);
    }
}
