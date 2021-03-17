<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Repositories\GoodRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var GoodRepository
     */
    protected $goodRepository;

    /**
     * CategoryService constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param GoodRepository $goodRepository
     */
    public function __construct(CategoryRepository $categoryRepository,
                                GoodRepository $goodRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->goodRepository = $goodRepository;
    }

    /**
     * get categories list
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getCategoriesList(int $limit): LengthAwarePaginator
    {
        return $this
            ->categoryRepository
            ->paginateAllWithRelations($limit);
    }

    /**
     * return parent - children categories
     *
     * @return Collection
     */
    public function getCategoriesListWithRelation(): Collection
    {
        return $this
            ->categoryRepository
            ->allCategoriesWithRelation('categoryInverse');
    }

    /**
     * return category collection
     *
     * @return Collection
     */
    public function getCategoriesCollection(): Collection
    {
        return $this
            ->categoryRepository
            ->all();
    }

    /**
     * to save a new category
     *
     * @param array $data
     * @return Model
     */
    public function storeCategories(array $data): Model
    {
        $data = $this->categoryRepository
            ->store($data);

        $data = $this->categoryRepository
            ->getWith($data, $data->id, 'category');

        return $data;
    }

    /**
     * to update existing category
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function updateCategories(array $data, int $id): Model
    {
        $data = $this->categoryRepository
            ->update($data, $id);

        return $data;
    }

    /**
     * delete record
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        if ($this->goodRepository->whereCount('category_id', $id)) {
            return false;
        } else {
            return $this->categoryRepository
                ->destroy($id);
        }
    }

    public function getCategory(int $id): Model
    {
        return $this->categoryRepository->getOne($id);
    }

    public function getCategoryBySlug(string $slug): Model
    {
        return $this->categoryRepository->getOneBySlug($slug);
    }

    /**
     * Get all names
     *
     * @return Collection
     */
    public function getNames(): Collection
    {
        return $this->categoryRepository
            ->allColumns(['id', 'title']);
    }
}
