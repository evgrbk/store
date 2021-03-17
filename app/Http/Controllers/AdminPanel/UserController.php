<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserRoleResource;
use App\Http\Requests\AdminPanel\User\IndexUserRequest;
use App\Http\Requests\AdminPanel\User\CreateUserRequest;
use App\Http\Requests\AdminPanel\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Auth;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserService $service
     */
    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    /**
     * Get all users
     *
     * @param IndexUserRequest $request
     * @return JsonResponse
     */
    public function index(IndexUserRequest $request): JsonResponse
    {
//        if (!Auth::user()->hasPermission('read-users')) {
//            return response()->json([
//                'status' => 'error',
//                'message' => 'Нет доступа',
//            ], 422);
//        }

        $allUsers = $this->userService
            ->getList($request->limit, $request->except('limit'));

        return response()->json([
            'data' => UserResource::collection($allUsers),
            'count' => $allUsers->total(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Display user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if (!Auth::user()->hasPermission('read-users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->userService
            ->getOne($id);

        return response()
            ->json(new UserResource($data)
                , JsonResponse::HTTP_OK);
    }

    /**
     * Create user.
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        if (!Auth::user()->hasPermission('create-users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $newNews = $this->userService
            ->create($request->all());

        return response()->json([
            'data' => new UserResource($newNews),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update user
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        if (!Auth::user()->hasPermission('update-users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->userService
            ->update($request->validated(), $id);

        return response()->json(new UserResource($data), JsonResponse::HTTP_OK);
    }

    /**
     * Delete user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        if (!Auth::user()->hasPermission('delete-users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->userService
            ->destroy($id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * Get roles for user
     *
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse
    {
        if (!Auth::user()->hasPermission('read-users')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Нет доступа',
            ], 422);
        }

        $data = $this->userService
            ->allRoles();

        return response()->json(UserRoleResource::collection($data), JsonResponse::HTTP_OK);
    }
}
