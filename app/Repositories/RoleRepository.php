<?php

namespace App\Repositories;

use App\Role;
use Illuminate\Database\Eloquent\Model;

class RoleRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Role();
    }

    /**
     * Get with relation
     *
     * @param int $id
     * @param string $with
     * @return Model
     */
    public function getWithColumn(int $id, string $with): Model
    {
        return $this->model->where('id', $id)
            ->with($with)
            ->first();
    }
}
