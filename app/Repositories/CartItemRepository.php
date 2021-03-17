<?php

namespace App\Repositories;

use App\Models\CartItem;

class CartItemRepository extends Repository
{
    public function __construct()
    {
        $this->model = new CartItem();
    }
}
