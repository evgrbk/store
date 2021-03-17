<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class Service
{
    /**
     * @var $repository
     */
    protected $repository;

    /**
     * To save a new record
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
//        dd($data);
        $data = $this->repository
            ->store($data);

        return $data;
    }

    /**
     * To update existing record
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {

        $data = $this->repository
            ->update($data, $id);

        return $data;
    }

    /**
     * delete record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $data = $this->repository
            ->destroy($id);

        return $data;
    }

    /**
     * return one record
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        $data = $this->repository
            ->getRecord($id);

        return $data;
    }
}
