<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Repositories\UserRepository;
use Modules\Core\Repositories\RoleRepository;
use Modules\Core\Repositories\NotificationRepository;
use Modules\Core\Repositories\WorkflowRepository;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\Auth;
//importa log
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
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
     * @var NotificationRepository
     */
    protected $notificationRepository;

    /**
     * @var WorkflowRepository
     */
    protected $workflowRepository;

    /**
     * DashboardController constructor.
     *
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param NotificationRepository $notificationRepository
     * @param WorkflowRepository $workflowRepository
     */
    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        NotificationRepository $notificationRepository,
        WorkflowRepository $workflowRepository
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->notificationRepository = $notificationRepository;
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * Display the dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        // Obtener estadísticas
        $stats = $this->getStats();

        // Obtener actividad reciente
        $recentActivities = AuditLog::with('user')
            ->orderBy('action_date', 'desc')
            ->limit(5)
            ->get();

        // Obtener notificaciones recientes
        $recentNotifications = $this->notificationRepository->getNotificationsByRecipient(
            Auth::user()->email,
            5
        );

        // Obtener estado de componentes del sistema
        $systemComponents = $this->getSystemComponents();
       
        return view('core::dashboard', compact(
            'stats',
            'recentActivities',
            'recentNotifications',
            'systemComponents'
        ));
    }

    /**
     * Get system statistics.
     *
     * @return array
     */
    protected function getStats()
    {
        $users = $this->userRepository->count();

        // Usuarios nuevos en el último mes
        $newUsers = $this->userRepository->query()
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        $roles = $this->roleRepository->count();

        // Total de permisos en el sistema
        $permissions = $this->roleRepository->getModel()
            ->join('permissions', 'roles.id', '=', 'permissions.role_id')
            ->count();

        // Notificaciones
        $notifications = $this->notificationRepository->getModel()->count();

        // Notificaciones sin leer
        $unreadNotifications = $this->notificationRepository->getModel()
            ->where('recipient', Auth::user()->email)
            ->whereRaw("NOT (JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.read')) = 'true')")
            ->count();

        // Workflows
        $workflows = $this->workflowRepository->count();

        // Workflows activos
        $activeWorkflows = $this->workflowRepository->getActiveWorkflows()->count();

        return [
            'users' => $users,
            'new_users' => $newUsers,
            'roles' => $roles,
            'permissions' => $permissions,
            'notifications' => $notifications,
            'unread_notifications' => $unreadNotifications,
            'workflows' => $workflows,
            'active_workflows' => $activeWorkflows
        ];
    }

    /**
     * Get system components status.
     *
     * @return array
     */
    protected function getSystemComponents()
    {
        // Esta función podría ser mucho más compleja y verificar
        // realmente el estado de diversos componentes del sistema

        return [
            [
                'name' => 'Core',
                'status' => 'active',
                'description' => 'Módulo principal del sistema'
            ],
            [
                'name' => 'Autenticación',
                'status' => 'active',
                'description' => 'Sistema de autenticación y autorización'
            ],
            [
                'name' => 'Notificaciones',
                'status' => 'active',
                'description' => 'Sistema de notificaciones'
            ],
            [
                'name' => 'Workflows',
                'status' => 'active',
                'description' => 'Motor de flujos de trabajo'
            ],
            [
                'name' => 'Base de datos',
                'status' => 'active',
                'description' => 'Conexión y funcionamiento de la base de datos'
            ],
            [
                'name' => 'Integración de correo',
                'status' => 'active',
                'description' => 'Integración con el servidor de correo'
            ]
        ];
    }
}
