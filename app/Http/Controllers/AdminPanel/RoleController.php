<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Http\Resources\RoleResource;
use App\Http\Requests\AdminPanel\Role\IndexRoleRequest;
use App\Http\Requests\AdminPanel\Role\CreateRoleRequest;
use App\Http\Requests\AdminPanel\Role\UpdateRoleRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Auth;

class RoleController extends Controller
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * RoleController constructor.
     *
     * @param RoleService $service
     */
    public function __construct(RoleService $service)
    {
        $this->roleService = $service;
    }

    /**
     * Get all roles
     *
     * @param IndexRoleRequest $request
     * @return JsonResponse
     */
    public function index(IndexRoleRequest $request): JsonResponse
    {
        if (!Auth::user()->hasPermission('read-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $allRoles = $this->roleService
            ->getList($request->limit);

        return response()->json([
            'data' => RoleResource::collection($allRoles),
            'count' => $allRoles->total(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Show role
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if (!Auth::user()->hasPermission('read-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->roleService
            ->getOne($id);

        return response()
            ->json(new RoleResource($data)
                , JsonResponse::HTTP_OK);
    }

    /**
     * Create role
     *
     * @param CreateRoleRequest $request
     * @return JsonResponse
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        if (!Auth::user()->hasPermission('create-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $newNews = $this->roleService
            ->createOrUpdate($request->all());

        return response()->json([
            'data' => new RoleResource($newNews),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update role
     *
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, $id): JsonResponse
    {
        if (!Auth::user()->hasPermission('update-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->roleService
            ->createOrUpdate($request->validated(), $id);

        return response()->json(new RoleResource($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete role
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        if (!Auth::user()->hasPermission('delete-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->roleService
            ->destroy($id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * Get all permissions
     *
     * @return JsonResponse
     */
    public function getPermissions(): JsonResponse
    {
        if (!Auth::user()->hasPermission('read-roles')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->roleService
            ->allPermissions();

        return response()->json(PermissionResource::collection($data), JsonResponse::HTTP_OK);
    }
}
