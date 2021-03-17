<?php

namespace App\Repositories;

use App\Models\Language;

class LanguageRepository extends Repository
{
    /**
     * LanguageRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Language();
    }
}
