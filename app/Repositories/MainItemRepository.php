<?php

namespace App\Repositories;

use App\Models\MainItem;
use Illuminate\Database\Eloquent\Model;

class MainItemRepository extends Repository
{
    /**
     * MainItemRepository constructor.
     */
    public function __construct()
    {
        $this->model = new MainItem();
    }

//    /**
//     * Create new image
//     *
//     * @param array $data
//     * @return Model
//     */
//    public function store(array $data): Model
//    {
//        return $this->model()
//            ->create($data);
//    }

    /**
     * Update record by id
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $model = $this
            ->model
            ->find($id);

        $model->update($data);

        return $model;
    }

}
