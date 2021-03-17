<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\FeatureGroup\FeatureGroupStoreRequest;
use App\Http\Requests\AdminPanel\FeatureGroup\FeatureGroupUpdateRequest;
use App\Services\FeatureGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeatureGroupController extends Controller
{
    public $service;

    public function __construct(FeatureGroupService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->checkPermission('read-feature-group');

        $limit = $request->get('limit');

        $data = $this->service
            ->getList($limit, $request->except('limit'));

        return response()
            ->json([
                'data' => $data->items(),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FeatureGroupStoreRequest $request
     * @return Response
     */
    public function store(FeatureGroupStoreRequest $request)
    {
        $this->checkPermission('create-feature-group');

        $model = $this->service->create($request->all());

        return response()->json($model, JsonResponse::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FeatureGroupUpdateRequest $request
     * @param int $id
     * @return Response
     */
    public function update(FeatureGroupUpdateRequest $request, $id)
    {
        $this->checkPermission('update-feature-group');

        $model = $this->service->update($request->all(), $id);

        return response()->json($model, JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->checkPermission('delete-feature-group');

        $this->service->delete($id);

        return response()->json([], 204);
    }
}
