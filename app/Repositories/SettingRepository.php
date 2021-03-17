<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;

class SettingRepository extends Repository
{
    /**
     * SettingRepository constructor.
     */
    public function __construct()
    {
        $this->model = new Setting();
    }

    /**
     * Get record by id
     *
     * @param int $id
     */
    public function getRecordById(int $id)
    {
        return $this->model
            ->find($id);
    }

    /**
     * Get record by name column
     *
     * @param string $name
     */
    public function getRecordByName(string $name)
    {
        return $this->model
            ->where('name', $name);
    }
}
