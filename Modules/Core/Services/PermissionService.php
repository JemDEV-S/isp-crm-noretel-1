<?php

namespace Modules\Core\Services;

use Modules\Core\Repositories\RoleRepository;
use Modules\Core\Repositories\UserRepository;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * PermissionService constructor.
     *
     * @param RoleRepository $roleRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        RoleRepository $roleRepository,
        UserRepository $userRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Check if user has permission
     *
     * @param int $userId
     * @param string $permission
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function hasPermission($userId, $permission, $module, $context = [])
    {
        $cacheKey = "user.{$userId}.permission.{$module}.{$permission}";

        return Cache::remember($cacheKey, 60, function () use ($userId, $permission, $module, $context) {
            $user = $this->userRepository->find($userId);

            foreach ($user->roles as $role) {
                if (!$role->active) {
                    continue;
                }

                foreach ($role->permissions as $perm) {
                    // Si tiene permiso de gestión completa del módulo
                    if ($perm->module === $module && $perm->action === 'manage' && $perm->allowed) {
                        return true;
                    }

                    // Si tiene el permiso específico solicitado
                    if ($perm->module === $module && $perm->action === $permission && $perm->allowed) {
                        // Si no hay condiciones, el permiso es válido
                        if (empty($perm->conditions)) {
                            return true;
                        }

                        // Verificar cada condición contra el contexto
                        $valid = true;
                        foreach ($perm->conditions as $key => $value) {
                            if (!isset($context[$key]) || $context[$key] != $value) {
                                $valid = false;
                                break;
                            }
                        }

                        if ($valid) {
                            return true;
                        }
                    }
                }
            }

            return false;
        });
    }

    /**
     * Check if user can view a module
     *
     * @param int $userId
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canViewModule($userId, $module, $context = [])
    {
        // Si tiene cualquier permiso de gestión o visualización
        return $this->hasPermission($userId, 'manage', $module, $context) ||
               $this->hasPermission($userId, 'view', $module, $context);
    }

    /**
     * Check if user can create in a module
     *
     * @param int $userId
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canCreateInModule($userId, $module, $context = [])
    {
        return $this->hasPermission($userId, 'manage', $module, $context) ||
               $this->hasPermission($userId, 'create', $module, $context);
    }

    /**
     * Check if user can edit in a module
     *
     * @param int $userId
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canEditInModule($userId, $module, $context = [])
    {
        return $this->hasPermission($userId, 'manage', $module, $context) ||
               $this->hasPermission($userId, 'edit', $module, $context);
    }

    /**
     * Check if user can delete in a module
     *
     * @param int $userId
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canDeleteInModule($userId, $module, $context = [])
    {
        return $this->hasPermission($userId, 'manage', $module, $context) ||
               $this->hasPermission($userId, 'delete', $module, $context);
    }

    /**
     * Get all permissions for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserPermissions($userId)
    {
        $cacheKey = "user.{$userId}.permissions";

        return Cache::remember($cacheKey, 60, function () use ($userId) {
            $user = $this->userRepository->find($userId);
            $permissions = [];

            foreach ($user->roles as $role) {
                if (!$role->active) {
                    continue;
                }

                foreach ($role->permissions as $perm) {
                    if ($perm->allowed) {
                        $key = "{$perm->module}.{$perm->action}";
                        $permissions[$key] = [
                            'module' => $perm->module,
                            'action' => $perm->action,
                            'conditions' => $perm->conditions
                        ];
                    }
                }
            }

            return $permissions;
        });
    }

    /**
     * Clear user permissions cache
     *
     * @param int $userId
     * @return void
     */
    public function clearUserPermissionsCache($userId)
    {
        Cache::forget("user.{$userId}.permissions");

        // También limpiamos las cachés de permisos individuales
        $user = $this->userRepository->find($userId);
        $permissions = $this->getUserPermissions($userId);

        foreach ($permissions as $permission) {
            $cacheKey = "user.{$userId}.permission.{$permission['module']}.{$permission['action']}";
            Cache::forget($cacheKey);
        }
    }

    /**
     * Create a new role
     *
     * @param array $roleData
     * @param array $permissions
     * @param int $createdBy
     * @param string $ip
     * @return array
     */
    public function createRole(array $roleData, array $permissions, $createdBy, $ip)
    {
        // Crear rol
        $role = $this->roleRepository->create($roleData);

        // Crear permisos para el rol
        $this->roleRepository->syncPermissions($role->id, $permissions);

        // Registrar acción
        AuditLog::register(
            $createdBy,
            'role_created',
            'permissions',
            "Rol creado: {$role->name}",
            $ip,
            null,
            ['role_id' => $role->id, 'role_name' => $role->name]
        );

        return [
            'success' => true,
            'role' => $role
        ];
    }

    /**
     * Update a role
     *
     * @param int $roleId
     * @param array $roleData
     * @param array $permissions
     * @param int $updatedBy
     * @param string $ip
     * @return array
     */
    public function updateRole($roleId, array $roleData, array $permissions, $updatedBy, $ip)
    {
        // Obtener rol antes de actualizar
        $oldRole = $this->roleRepository->getRoleWithPermissions($roleId);

        // Actualizar rol
        $this->roleRepository->update($roleId, $roleData);

        // Sincronizar permisos
        $this->roleRepository->syncPermissions($roleId, $permissions);

        // Obtener rol actualizado
        $updatedRole = $this->roleRepository->getRoleWithPermissions($roleId);

        // Registrar acción
        AuditLog::register(
            $updatedBy,
            'role_updated',
            'permissions',
            "Rol actualizado: {$updatedRole->name}",
            $ip,
            [
                'name' => $oldRole->name,
                'description' => $oldRole->description,
                'active' => $oldRole->active,
                'permissions' => $oldRole->permissions->toArray()
            ],
            [
                'name' => $updatedRole->name,
                'description' => $updatedRole->description,
                'active' => $updatedRole->active,
                'permissions' => $updatedRole->permissions->toArray()
            ]
        );

        // Limpiar caché de permisos para todos los usuarios con este rol
        $users = $updatedRole->users;
        foreach ($users as $user) {
            $this->clearUserPermissionsCache($user->id);
        }

        return [
            'success' => true,
            'role' => $updatedRole
        ];
    }

    /**
     * Assign role to user
     *
     * @param int $userId
     * @param int $roleId
     * @param int $assignedBy
     * @param string $ip
     * @return array
     */
    public function assignRoleToUser($userId, $roleId, $assignedBy, $ip)
    {
        $user = $this->userRepository->find($userId);
        $role = $this->roleRepository->find($roleId);

        // Verificar si el usuario ya tiene el rol
        if ($user->roles->contains('id', $roleId)) {
            return [
                'success' => false,
                'message' => "El usuario ya tiene el rol {$role->name}."
            ];
        }

        // Asignar rol
        $this->roleRepository->assignToUser($roleId, $userId);

        // Registrar acción
        AuditLog::register(
            $assignedBy,
            'role_assigned',
            'permissions',
            "Rol {$role->name} asignado al usuario {$user->username}",
            $ip,
            null,
            ['role_id' => $roleId, 'user_id' => $userId]
        );

        // Limpiar caché de permisos
        $this->clearUserPermissionsCache($userId);

        return [
            'success' => true,
            'message' => "Rol {$role->name} asignado correctamente."
        ];
    }

    /**
     * Remove role from user
     *
     * @param int $userId
     * @param int $roleId
     * @param int $removedBy
     * @param string $ip
     * @return array
     */
    public function removeRoleFromUser($userId, $roleId, $removedBy, $ip)
    {
        $user = $this->userRepository->find($userId);
        $role = $this->roleRepository->find($roleId);

        // Verificar si el usuario tiene el rol
        if (!$user->roles->contains('id', $roleId)) {
            return [
                'success' => false,
                'message' => "El usuario no tiene el rol {$role->name}."
            ];
        }

        // Eliminar rol
        $this->roleRepository->removeFromUser($roleId, $userId);

        // Registrar acción
        AuditLog::register(
            $removedBy,
            'role_removed',
            'permissions',
            "Rol {$role->name} removido del usuario {$user->username}",
            $ip,
            ['role_id' => $roleId, 'user_id' => $userId],
            null
        );

        // Limpiar caché de permisos
        $this->clearUserPermissionsCache($userId);

        return [
            'success' => true,
            'message' => "Rol {$role->name} removido correctamente."
        ];
    }
}
