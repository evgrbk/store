<?php

namespace App\Repositories;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends Repository
{
    public function __construct()
    {
        $this->model = new User();
    }

}
