<?php

namespace App\Services;

use App\Models\FeatureGroup;

class FeatureGroupService
{
    public $featureGroupModel;

    public function __construct(FeatureGroup $featureGroupModel)
    {
        $this->featureGroupModel = $featureGroupModel;
    }

    public function getList(int $limit, array $params = [])
    {
        return $this->featureGroupModel
            ->with(['features'])
            ->paginate($limit);
    }

    public function store(array $data, $model)
    {
        $sync = array_map(function ($feature) {
            return [
                'feature_id' => $feature
            ];
        }, $data['features']);
        $model->fill($data)->save();

        $model->features()->sync($sync);

        return $model;
    }

    public function create(array $data)
    {
        return $this->store($data, new $this->featureGroupModel());
    }

    public function update(array $data, int $id)
    {
        $model = $this->featureGroupModel->find($id);

        return $this->store($data, $model);
    }

    public function delete(int $id)
    {
        return $this->featureGroupModel->destroy($id);
    }
}
