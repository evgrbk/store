<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\Features\StoreRequest;
use App\Services\FeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public $service;

    public function __construct(FeatureService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->checkPermission('read-features');

        $limit = $request->get('limit');

        $data = $this->service
            ->getList($limit, $request->except('limit'));

        return response()
            ->json([
                'data' => $data->items(),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    public function store(StoreRequest $request)
    {
        $this->checkPermission('create-features');

        $model = $this->service->create($request->validated());

        return response()->json($model, JsonResponse::HTTP_CREATED);
    }

    public function update(StoreRequest $request, $id)
    {
        $this->checkPermission('update-features');

        $model = $this->service->update($request->validated(), $id);

        return response()->json($model, JsonResponse::HTTP_OK);
    }

    public function destroy($id)
    {
        $this->checkPermission('delete-features');

        $this->service->delete($id);

        return response()->json([], 204);
    }

}
