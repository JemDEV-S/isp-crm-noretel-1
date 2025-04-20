<?php

namespace Modules\Core\Repositories;

use Modules\Core\Entities\Role;

class RoleRepository extends BaseRepository
{
    /**
     * RoleRepository constructor.
     *
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Get active roles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveRoles()
    {
        return $this->model->where('active', true)->get();
    }

    /**
     * Get role with permissions
     *
     * @param int $id
     * @return Role
     */
    public function getRoleWithPermissions($id)
    {
        return $this->model->with('permissions')->findOrFail($id);
    }

    /**
     * Get role by name
     *
     * @param string $name
     * @return Role|null
     */
    public function findByName($name)
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Sync role permissions
     *
     * @param int $roleId
     * @param array $permissions Array of permission data
     * @return Role
     */
    public function syncPermissions($roleId, array $permissions)
    {
        $role = $this->find($roleId);
        
        // Eliminar permisos actuales
        $role->permissions()->delete();
        
        // Crear nuevos permisos
        foreach ($permissions as $permission) {
            $role->permissions()->create($permission);
        }
        
        return $role->fresh('permissions');
    }

    /**
     * Assign role to user
     *
     * @param int $roleId
     * @param int $userId
     * @return void
     */
    public function assignToUser($roleId, $userId)
    {
        $role = $this->find($roleId);
        $role->users()->attach($userId);
    }

    /**
     * Remove role from user
     *
     * @param int $roleId
     * @param int $userId
     * @return void
     */
    public function removeFromUser($roleId, $userId)
    {
        $role = $this->find($roleId);
        $role->users()->detach($userId);
    }
}