<?php

namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class FaqRepository extends Repository
{
    /**
     * Create new model
     *
     * FaqRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Faq();
    }

    /**
     * Create new faq
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->model
            ->create($data);
    }

    /**
     * Get one faq record
     *
     * @param int $id
     * @return Model
     */
    public function getRecord(int $id): Model
    {
        return $this
            ->model
            ->with('images')
            ->findOrFail($id);
    }

    /**
     * Delete one faq by id
     *
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        return $this->model
            ->destroy($id);
    }

    /**
     * Get all records with pagination
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAllWithRelation(int $limit): LengthAwarePaginator
    {
        $namespace = $this->model
            ->namespace();

        return $this
            ->model
//            ->with(['images' => function ($q) use ($namespace) {
//                $q->where('media.model_type', $namespace);
//            }])
            ->with('images')
            ->paginate($limit);
    }
}
