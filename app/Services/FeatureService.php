<?php

namespace App\Services;

use App\Models\Feature;

class FeatureService
{
	public $featureModel;

	public function __construct(Feature $featureModel)
	{
		$this->featureModel = $featureModel;
	}

	public function getList(int $limit, array $params = [])
    {
        return $this->featureModel->paginate($limit);
    }

	public function create(array $data)
	{
		return $this->featureModel->create($data);
	}

	public function update(array $data, int $id)
	{
		$model = $this->featureModel->find($id);

		$model->fill($data)->save();

		return $model;	
	}

	public function delete(int $id)
	{
		return $this->featureModel->destroy($id);
	}
}