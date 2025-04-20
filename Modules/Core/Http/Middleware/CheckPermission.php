<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\PermissionService;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * CheckPermission constructor.
     *
     * @param PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $module
     * @param string $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $module, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('core.auth.login');
        }

        $context = [];

        // Si hay ruta con parámetro, agregarlo al contexto
        if ($request->route('id')) {
            $context['entity_id'] = $request->route('id');
        }

        // Primero verificamos si tiene permiso de gestión completa del módulo
        if ($this->permissionService->hasPermission(Auth::id(), 'manage', $module, $context)) {
            return $next($request);
        }

        // Si no tiene permiso de gestión, verificamos el permiso específico
        if (!$this->permissionService->hasPermission(Auth::id(), $permission, $module, $context)) {
            abort(403, 'No tiene permiso para acceder a este recurso.');
        }

        return $next($request);
    }
}
