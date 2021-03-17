<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;
use Exception;

class RoleService extends Service
{
    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * RoleService constructor.
     *
     * @param RoleRepository $roleRepository
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit): LengthAwarePaginator
    {
        $roles = $this->roleRepository
            ->paginateAll($limit);

        return $roles;
    }

    /**
     * Get role
     *
     * @param int $id
     * @return Model
     */
    public function getOne(int $id): Model
    {
        $model = $this->roleRepository
            ->getWithColumn($id, 'permissions');

        return $model;
    }

    /**
     * Create or update role
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function createOrUpdate(array $data, int $id = 0): Model
    {
        try {
            DB::beginTransaction();

            $role = [
                'name' => $data['name'],
                'description' => $data['description']
            ];

            if ($id) {
                $model = $this
                    ->roleRepository
                    ->update($role, $id);
            } else {
                $model = $this->roleRepository->store($role);
            }

            if (!empty($data['permissions'])) {
                $model->syncPermissions($data['permissions']);
            }

            DB::commit();
            return $model;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete role
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function destroy(int $id): bool
    {
        return $this->roleRepository
            ->destroy($id);
    }

    /**
     * Get permissions
     *
     * @return Collection
     * @return JsonResponse
     */
    public function allPermissions(): Collection
    {
        return $this->permissionRepository
            ->all();
    }

}
