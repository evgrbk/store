<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Good;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class GoodRepository extends Repository
{
    /**
     * @var MediaRepository
     */
    public MediaRepository $mediaRepository;

    /**
     * Set a model for main Repository
     *
     * GoodRepository constructor.
     * @param MediaRepository $mediaRepository
     */
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
        $this->model = new Good();
    }

    /**
     * Create new good
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        return $this->model()
            ->create($data);
    }

    /**
     * Get one good by id with media
     *
     * @param int $id
     * @return Model
     */
    public function getRecordWithCategoryWithMedia(int $id): Model
    {
        $namespace = $this->model
            ->namespace();

        return $this->model
            ->with(['files' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->with('category.category')
            ->find($id);
    }

    public function getRecordWithCategoryWithMediaBySlug(string $slug): Model
    {
        $namespace = $this->model
            ->namespace();

        return $this->model
            ->with(['files' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->with('category.category', 'translates')
            ->where('seo_slug', $slug)
            ->first();
    }

    /**
     * Get good with media
     *
     * @param Model $good
     * @return Model
     */
    public function getWithMediaWithCategory(model $good): Model
    {
        $namespace = $this->model
            ->namespace();

        return $good->with(['files' => function ($q) use ($namespace) {
            $q->where('media.model_type', $namespace);
        }])
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }])
            ->with('category.category')
            ->where('id', $good->id)
            ->first();
    }

    /**
     * Get all goods with pagination
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function paginateAllGoodsWithMediaWithCategories(int $limit, array $params): LengthAwarePaginator
    {
        $namespace = $this->model
            ->namespace();

        $query = $this
            ->model
            ->with('category.category', 'features', 'brand');

        $query = $this->withFilters($query, $params);

        $query = $this->withFiles($query, $namespace);
        $query = $this->withImages($query, $namespace);

        $query = $this->withOrders($query, $params);

        return $query->paginate($limit);
    }

    /**
     * Make query for filter
     *
     * @return Builder
     */
    public function getGoodsQueryWithFilter(): Builder
    {
        $namespace = $this->model
            ->namespace();

        $query = $this
            ->model
            ->newQuery();

        $query->with('ratings', 'translates');

        $query = $this->withFiles($query, $namespace);
        $query = $this->withImages($query, $namespace);
        return $query;
    }

    /**
     * Get paginate
     *
     * @param $query
     * @param $limit
     * @return LengthAwarePaginator
     */
    public function getPaginate($query, $limit): LengthAwarePaginator
    {
        return $query->paginate($limit);
    }

    protected function getSubcategoriesIds($category)
    {
        if ($category->categoryInverse->isEmpty()) {
            return [$category->id];
        }

        $ids = [];

        foreach ($category->categoryInverse as $subcategory) {
            $ids = array_merge($ids, $this->getSubcategoriesIds($subcategory));
        }

        return array_merge($ids, [$category->id]);
    }

    /**
     * return query with category by id
     *
     * @param Builder $query
     * @param int $categoryId
     * @return Builder
     */
    public function whereCategories(Builder $query, int $categoryId): Builder
    {
        $category = Category::find($categoryId);

        $ids = $this->getSubcategoriesIds($category);

        return $query->whereIn('category_id', $ids);
    }

    public function filterByPrice(Builder $query, array $data): Builder
    {

        if (isset($data['min_price'])) {
            $query->where('price_integer', '>=', $data['min_price']);
        }

        if (isset($data['max_price'])) {
            $query->where('price_integer', '<=', $data['max_price']);
        }


        return $query;
    }

    public function filterByQuery(Builder $query, string $filter): Builder
    {

        $query->where('good_title', 'like', $filter . '%');

        return $query;
    }

    /**
     * return query with files
     *
     * @param Builder $query
     * @param string $namespace
     * @return Builder
     */
    public function withFiles(Builder $query, string $namespace): Builder
    {
        return $query
            ->with(['files' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }]);
    }

    /**
     * return query with images
     *
     * @param Builder $query
     * @param string $namespace
     * @return Builder
     */
    public function withImages(Builder $query, string $namespace): Builder
    {
        return $query
            ->with(['images' => function ($q) use ($namespace) {
                $q->where('media.model_type', $namespace);
            }]);
    }


    public function withSort(Builder $query, array $data): Builder
    {
        $sortDirection = isset($data['sort_direction']) ? $data['sort_direction'] : 'ASC';

        if (isset($data['sort_by'])) {
            $query->orderBy($data['sort_by'], $sortDirection);
        }

        return $query;
    }


    /**
     * reserve a good for a purchase
     *
     * @param int $id
     * @param int $count
     */
    public function reserveGood(int $id, int $count)
    {
        $good = $this->getRecord($id);
        if ($good->good_type != Good::TYPE_LIMITED) return;
        $good->increment('count_reserved', $count);
    }

    /**
     * unreserve a good
     *
     * @param int $id
     * @param int $count
     */
    public function unreserveGood(int $id, int $count)
    {
        $good = $this->getRecord($id);
        if ($good->good_type != Good::TYPE_LIMITED) return;
        $good->decrement('count_reserved', $count);
    }

    public function active(Builder $query)
    {
        return $query->where('active', 1);
    }

    /**
     * Where count
     *
     * @param string $column
     * @param int $id
     * @return int
     */
    public function whereCount(string $column, int $id): int
    {

        return $this->model
            ->where($column, $id)
            ->count();
    }

    /**
     * Add filters
     *
     * @param Builder $query
     * @param array $params
     * @return Builder
     */
    public function withFilters(Builder $query, array $params): Builder
    {
        if (isset($params['category'])) {
            $query->whereHas("category", function ($q) use ($params) {
                $q->where('title', 'like', '%' . $params['category'] . '%');
            });
        }
        if (isset($params['id'])) {
            $query->where('id', '=', $params['id']);
        }
        if (isset($params['good_title'])) {
            $query->where('good_title', 'like', '%' . $params['good_title'] . '%');
        }
        if (isset($params['good_description'])) {
            $query->where('good_description', 'like', '%' . $params['good_description'] . '%');
        }
        if (isset($params['good_left'])) {
            $query->where('good_left', '=', $params['good_left']);
        }
        if (isset($params['seo_slug'])) {
            $query->where('seo_slug', 'like', '%' . $params['seo_slug'] . '%');
        }
        if (isset($params['active'])) {
            $query->where('active', '=', $params['active']);
        }

        return $query;
    }

    /**
     * Add orders
     *
     * @param Builder $query
     * @param array $params
     * @param array $columns
     * @return Builder
     */
    public function withOrders(Builder $query, array $params, array $columns = ['*']): Builder
    {
        if (isset($params['orderBy']) && isset($params['ascending'])) {
            if ($params['orderBy'] == 'category') {
                $columns = array_map(function ($value) {
                    if ($value === 'category') {
                        return 'categories.title';
                    } else {
                        return 'goods.' . $value;
                    }
                }, $columns);
                $query->join('categories', 'categories.id', '=', 'goods.category_id')
                    ->orderBy('categories.title', $params['ascending'] ? 'ASC' : 'DESC')
                    ->select($columns);
            } else {
                $query->orderBy($params['orderBy'], $params['ascending'] ? 'ASC' : 'DESC');
            }
        };

        return $query;
    }

    /**
     * Get all goods with filters
     *
     * @param array $params
     * @param array $columns
     * @return Collection
     */
    public function getWithFiltersAndOrders(array $params, array $columns): Collection
    {
        $query = $this
            ->model
            ->with('category');

        $query = $this->withFilters($query, $params);
        $query = $this->withOrders($query, $params, $columns);

        return $query->get($columns);
    }
}
