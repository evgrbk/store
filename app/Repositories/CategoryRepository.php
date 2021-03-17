<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends Repository
{
    /**
     * Create new model
     *
     * CategoryRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Category();
    }

    /**
     * Get all records with pagination with relation
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAllWithRelations(int $limit): LengthAwarePaginator
    {
        return $this
            ->model
            ->with( 'category', 'goods')
            ->paginate($limit);
    }

    /**
     * return parent - child categories
     *
     * @param string $with
     * @return Collection
     */
    public function allCategoriesWithRelation(string $with): Collection
    {
        return $this
            ->model
            ->with($with)
            ->withCount('goods')
            ->where('category_id', null)
            ->get();
    }

    public function getOne(int $id): Model
    {
        return $this->model->find($id);
    }

    public function getOneBySlug(string $slug): Model
    {
        return $this->model->where('seo_slug', $slug)->first();
    }
}
