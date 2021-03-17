<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * to get a Model
     *
     * @return Model
     */
    public function model(): Model;

    /**
     * to get a list of records
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * return a list of records using pagination
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAll(int $limit): LengthAwarePaginator;
}
