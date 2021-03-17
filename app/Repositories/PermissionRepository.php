<?php

namespace App\Repositories;

use App\Permission;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Permission();
    }
}
