<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRepository extends Repository
{
    /**
     * CurrencyRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Currency();
    }


}
