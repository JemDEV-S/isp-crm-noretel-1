<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Repositories\UserRepository;
use Modules\Core\Repositories\RoleRepository;
use Modules\Core\Services\PermissionService;
use Modules\Core\Http\Requests\StoreUserRequest;
use Modules\Core\Http\Requests\UpdateUserRequest;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param PermissionService $permissionService
     */
    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PermissionService $permissionService
    ) {
        $this->userRepository = $userRepository;
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
        
        $query = $this->userRepository->query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $users = $query->paginate($perPage);
        
        return view('core::users.index', compact('users', 'search', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $roles = $this->roleRepository->getActiveRoles();
        return view('core::users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        
        // Hashear la contraseña
        $data['password'] = Hash::make($data['password']);
        
        // Crear usuario
        $user = $this->userRepository->create($data);
        
        // Asignar roles si se especificaron
        if ($request->has('roles')) {
            foreach ($request->roles as $roleId) {
                $this->permissionService->assignRoleToUser(
                    $user->id,
                    $roleId,
                    Auth::id(),
                    $request->ip()
                );
            }
        }
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'user_created',
            'users',
            "Usuario creado: {$user->username}",
            $request->ip(),
            null,
            $user->toArray()
        );
        
        return redirect()->route('core.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $user = $this->userRepository->find($id);
        
        // Obtener logs de auditoría del usuario
        $logs = AuditLog::where('user_id', $id)
            ->orderBy('action_date', 'desc')
            ->limit(50)
            ->get();
            
        return view('core::users.show', compact('user', 'logs'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $user = $this->userRepository->find($id);
        $roles = $this->roleRepository->getActiveRoles();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        return view('core::users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateUserRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userRepository->find($id);
        $data = $request->validated();
        
        // Si se proporciona una nueva contraseña, hashearla
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        
        // Guardar datos anteriores para auditoría
        $oldData = $user->toArray();
        
        // Actualizar usuario
        $this->userRepository->update($id, $data);
        
        // Actualizar roles si se especificaron
        if ($request->has('roles')) {
            // Eliminar roles actuales
            foreach ($user->roles as $role) {
                $this->permissionService->removeRoleFromUser(
                    $user->id,
                    $role->id,
                    Auth::id(),
                    $request->ip()
                );
            }
            
            // Asignar nuevos roles
            foreach ($request->roles as $roleId) {
                $this->permissionService->assignRoleToUser(
                    $user->id,
                    $roleId,
                    Auth::id(),
                    $request->ip()
                );
            }
        }
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'user_updated',
            'users',
            "Usuario actualizado: {$user->username}",
            $request->ip(),
            $oldData,
            $user->fresh()->toArray()
        );
        
        return redirect()->route('core.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $user = $this->userRepository->find($id);
        
        // No permitir eliminar el propio usuario
        if ($id == Auth::id()) {
            return redirect()->route('core.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        // Guardar datos para auditoría
        $userData = $user->toArray();
        
        // Eliminar usuario
        $this->userRepository->delete($id);
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'user_deleted',
            'users',
            "Usuario eliminado: {$user->username}",
            $request->ip(),
            $userData,
            null
        );
        
        return redirect()->route('core.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Activate a user
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function activate($id, Request $request)
    {
        $user = $this->userRepository->find($id);
        
        // Actualizar estado
        $this->userRepository->update($id, ['status' => 'active']);
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'user_activated',
            'users',
            "Usuario activado: {$user->username}",
            $request->ip(),
            ['status' => $user->status],
            ['status' => 'active']
        );
        
        return redirect()->route('core.users.index')
            ->with('success', 'Usuario activado correctamente.');
    }

    /**
     * Deactivate a user
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate($id, Request $request)
    {
        $user = $this->userRepository->find($id);
        
        // No permitir desactivar el propio usuario
        if ($id == Auth::id()) {
            return redirect()->route('core.users.index')
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }
        
        // Actualizar estado
        $this->userRepository->update($id, ['status' => 'inactive']);
        
        // Registrar acción
        AuditLog::register(
            Auth::id(),
            'user_deactivated',
            'users',
            "Usuario desactivado: {$user->username}",
            $request->ip(),
            ['status' => $user->status],
            ['status' => 'inactive']
        );
        
        return redirect()->route('core.users.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Get user permissions
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions(Request $request)
    {
        $permissions = $this->permissionService->getUserPermissions(Auth::id());
        
        return response()->json([
            'success' => true,
            'permissions' => $permissions
        ]);
    }
}