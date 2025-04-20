<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Repositories\RoleRepository;
use Modules\Core\Services\PermissionService;
use Modules\Core\Http\Requests\StoreRoleRequest;
use Modules\Core\Http\Requests\UpdateRoleRequest;
use Modules\Core\Http\Requests\SyncPermissionsRequest;
use Modules\Core\Http\Requests\AssignRoleRequest;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * RoleController constructor.
     *
     * @param RoleRepository $roleRepository
     * @param PermissionService $permissionService
     */
    public function __construct(
        RoleRepository $roleRepository,
        PermissionService $permissionService
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 10);
        
        $query = $this->roleRepository->query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($status === '1' || $status === '0') {
            $query->where('active', $status);
        }
        
        $roles = $query->paginate($perPage);
        
        return view('core::roles.index', compact('roles', 'search', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // Obtener lista de módulos y acciones disponibles
        $modules = $this->getAvailableModules();
        
        return view('core::roles.create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();
        
        // Crear rol
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'description' => $data['description'],
            'active' => $data['active'] ?? false,
            'default_permissions' => $data['default_permissions'] ?? null
        ]);
        
        // Asignar permisos iniciales si se especificaron
        if (!empty($data['permissions'])) {
            $this->roleRepository->syncPermissions($role->id, $this->formatPermissions($data['permissions']));
        }
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'role_created',
            'roles',
            "Rol creado: {$role->name}",
            $request->ip(),
            null,
            $role->toArray()
        );
        
        return redirect()->route('core.roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $role = $this->roleRepository->getRoleWithPermissions($id);
        
        // Obtener usuarios que tienen este rol
        $users = $role->users()->paginate(10);
        
        // Agrupar permisos por módulo para mostrarlos organizados
        $permissionsByModule = [];
        
        foreach ($role->permissions as $permission) {
            if (!isset($permissionsByModule[$permission->module])) {
                $permissionsByModule[$permission->module] = [];
            }
            $permissionsByModule[$permission->module][] = $permission;
        }
        
        return view('core::roles.show', compact('role', 'users', 'permissionsByModule'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $role = $this->roleRepository->getRoleWithPermissions($id);
        
        // Obtener lista de módulos y acciones disponibles
        $modules = $this->getAvailableModules();
        
        // Preparar array con permisos actuales para pre-seleccionarlos en el formulario
        $currentPermissions = [];
        
        foreach ($role->permissions as $permission) {
            $key = "{$permission->module}|{$permission->action}";
            $currentPermissions[$key] = true;
        }
        
        return view('core::roles.edit', compact('role', 'modules', 'currentPermissions'));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = $this->roleRepository->find($id);
        $data = $request->validated();
        
        // Guardar datos anteriores para auditoría
        $oldData = $role->toArray();
        
        // Actualizar rol
        $this->roleRepository->update($id, [
            'name' => $data['name'],
            'description' => $data['description'],
            'active' => $data['active'] ?? false,
            'default_permissions' => $data['default_permissions'] ?? null
        ]);
        
        // Sincronizar permisos si se especificaron
        if (isset($data['permissions'])) {
            $this->roleRepository->syncPermissions($id, $this->formatPermissions($data['permissions']));
        }
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'role_updated',
            'roles',
            "Rol actualizado: {$role->name}",
            $request->ip(),
            $oldData,
            $role->fresh()->toArray()
        );
        
        return redirect()->route('core.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $role = $this->roleRepository->find($id);
        
        // Verificar si el rol está en uso
        if ($role->users()->count() > 0) {
            return redirect()->route('core.roles.index')
                ->with('error', 'No se puede eliminar un rol que está asignado a usuarios.');
        }
        
        // Guardar datos para auditoría
        $roleData = $role->toArray();
        
        // Eliminar rol
        $this->roleRepository->delete($id);
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'role_deleted',
            'roles',
            "Rol eliminado: {$role->name}",
            $request->ip(),
            $roleData,
            null
        );
        
        return redirect()->route('core.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Sync permissions for a role.
     * @param SyncPermissionsRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function syncPermissions(SyncPermissionsRequest $request, $id)
    {
        $role = $this->roleRepository->find($id);
        $data = $request->validated();
        
        // Guardar permisos anteriores para auditoría
        $oldPermissions = $role->permissions->toArray();
        
        // Sincronizar permisos
        $this->roleRepository->syncPermissions($id, $this->formatPermissions($data['permissions']));
        
        // Obtener rol actualizado con sus permisos
        $updatedRole = $this->roleRepository->getRoleWithPermissions($id);
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'permissions_synced',
            'roles',
            "Permisos sincronizados para el rol: {$role->name}",
            $request->ip(),
            ['permissions' => $oldPermissions],
            ['permissions' => $updatedRole->permissions->toArray()]
        );
        
        return redirect()->route('core.roles.show', $id)
            ->with('success', 'Permisos actualizados correctamente.');
    }

    /**
     * Assign role to user.
     * @param AssignRoleRequest $request
     * @return \Illuminate\Http\Response
     */
    public function assignToUser(AssignRoleRequest $request)
    {
        $data = $request->validated();
        
        $result = $this->permissionService->assignRoleToUser(
            $data['user_id'],
            $data['role_id'],
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }
        
        return back()->with('success', $result['message']);
    }

    /**
     * Remove role from user.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);
        
        $result = $this->permissionService->removeRoleFromUser(
            $request->user_id,
            $request->role_id,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }
        
        return back()->with('success', $result['message']);
    }

    /**
     * Get available modules and their actions.
     * 
     * @return array
     */
    private function getAvailableModules()
    {
        // En una implementación real, esto podría cargarse desde la base de datos
        // o ser detectado dinámicamente a partir de las clases del sistema
        
        return [
            'users' => [
                'name' => 'Usuarios',
                'actions' => [
                    'view' => 'Ver usuarios',
                    'create' => 'Crear usuarios',
                    'edit' => 'Editar usuarios',
                    'delete' => 'Eliminar usuarios',
                    'manage' => 'Gestionar usuarios (todos los permisos)'
                ]
            ],
            'roles' => [
                'name' => 'Roles y Permisos',
                'actions' => [
                    'view' => 'Ver roles',
                    'create' => 'Crear roles',
                    'edit' => 'Editar roles',
                    'delete' => 'Eliminar roles',
                    'manage' => 'Gestionar roles (todos los permisos)'
                ]
            ],
            'configuration' => [
                'name' => 'Configuraciones',
                'actions' => [
                    'view' => 'Ver configuraciones',
                    'edit' => 'Editar configuraciones',
                    'manage' => 'Gestionar configuraciones (todos los permisos)'
                ]
            ],
            'notifications' => [
                'name' => 'Notificaciones',
                'actions' => [
                    'view' => 'Ver notificaciones',
                    'create' => 'Crear notificaciones',
                    'manage' => 'Gestionar notificaciones (todos los permisos)'
                ]
            ],
            'workflows' => [
                'name' => 'Workflows',
                'actions' => [
                    'view' => 'Ver workflows',
                    'create' => 'Crear workflows',
                    'edit' => 'Editar workflows',
                    'execute' => 'Ejecutar transiciones',
                    'manage' => 'Gestionar workflows (todos los permisos)'
                ]
            ],
            'security' => [
                'name' => 'Seguridad',
                'actions' => [
                    'view' => 'Ver políticas de seguridad',
                    'edit' => 'Editar políticas de seguridad',
                    'manage' => 'Gestionar seguridad (todos los permisos)'
                ]
            ],
            'audit' => [
                'name' => 'Auditoría',
                'actions' => [
                    'view' => 'Ver logs de auditoría',
                    'export' => 'Exportar logs',
                    'manage' => 'Gestionar auditoría (todos los permisos)'
                ]
            ]
        ];
    }

    /**
     * Format permissions data for storage.
     * 
     * @param array $permissionsData
     * @return array
     */
    private function formatPermissions($permissionsData)
    {
        $formattedPermissions = [];
        
        foreach ($permissionsData as $permissionKey) {
            list($module, $action) = explode('|', $permissionKey);
            
            $formattedPermissions[] = [
                'module' => $module,
                'action' => $action,
                'allowed' => true,
                'conditions' => null
            ];
        }
        
        return $formattedPermissions;
    }
}