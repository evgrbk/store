<?php

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Cart();
    }

    /**
     * Get where with relations
     *
     * @param string $column
     * @param string $where
     * @return Collection
     */
    public function getWhereWithRelations(string $column, string $where): Collection
    {
        return $this->model
            ->where($column, $where)
            ->with('cartItems.good.ratings', 'cartItems.good.translates')
            ->get();
    }
}
