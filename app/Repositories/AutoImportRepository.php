<?php

namespace App\Repositories;

use App\Models\AutoImport;

class AutoImportRepository extends Repository
{
    public function __construct()
    {
        $this->model = new AutoImport();
    }
}
