<?php

namespace App\Repositories;

use App\Models\OrderGood;
use Illuminate\Database\Eloquent\Model;

class OrderGoodRepository extends Repository
{
    public function __construct()
    {
        $this->model = new OrderGood();
    }

    /**
     * To save a many items
     *
     * @param array $data
     * @return bool
     */
    public function storeMany(array $data): bool
    {
        return $this->model
            ->newQuery()
            ->insert($data);
    }
}
