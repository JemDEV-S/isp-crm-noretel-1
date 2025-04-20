<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\RoleController;
use Modules\Core\Http\Controllers\ConfigurationController;
use Modules\Core\Http\Controllers\NotificationController;
use Modules\Core\Http\Controllers\WorkflowController;
use Modules\Core\Http\Controllers\SecurityPolicyController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\AuditController;
use Modules\Core\Http\Middleware\CheckPermission;


// Grupo de rutas públicas
Route::prefix('core')->name('core.')->group(function() {

    // Rutas de autenticación
    Route::prefix('auth')->name('auth.')->group(function() {
        // Login
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);

        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Registro
        Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [AuthController::class, 'register']);

        // Recuperación de contraseña
        Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });
});

// Grupo de rutas protegidas por autenticación
Route::middleware('auth')->prefix('core')->name('core.')->group(function() {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cambio de contraseña
    Route::get('change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('change-password', [AuthController::class, 'changePassword']);

    // Gestión de usuarios - Con permisos granulares
    Route::prefix('users')->name('users.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:users,create')->group(function() {
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('', [UserController::class, 'store'])->name('store');
        });

        // Rutas de visualización - Deben ir después de 'create' para evitar conflictos
        Route::middleware('permission:users,view')->group(function() {
            Route::get('', [UserController::class, 'index'])->name('index');
            Route::get('{id}', [UserController::class, 'show'])->name('show')->where('id', '[0-9]+');
        });

        // Rutas de edición
        Route::middleware('permission:users,edit')->group(function() {
            Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('{id}', [UserController::class, 'update'])->name('update');
            Route::post('{id}/activate', [UserController::class, 'activate'])->name('activate');
            Route::post('{id}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
        });

        // Rutas de eliminación
        Route::middleware('permission:users,delete')->group(function() {
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        });
    });

    // Gestión de roles y permisos - Con permisos granulares
    Route::prefix('roles')->name('roles.')->group(function() {
        // Rutas de creación
        Route::middleware('permission:roles,create')->group(function() {
            Route::get('create', [RoleController::class, 'create'])->name('create');
            Route::post('', [RoleController::class, 'store'])->name('store');
        });

        // Rutas de visualización
        Route::middleware('permission:roles,view')->group(function() {
            Route::get('', [RoleController::class, 'index'])->name('index');
            Route::get('{id}', [RoleController::class, 'show'])->name('show');
        });

        // Rutas de edición
        Route::middleware('permission:roles,edit')->group(function() {
            Route::get('{id}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('{id}', [RoleController::class, 'update'])->name('update');
            Route::post('{id}/permissions', [RoleController::class, 'syncPermissions'])->name('permissions.sync');
            Route::post('assign-to-user', [RoleController::class, 'assignToUser'])->name('assign');
            Route::post('remove-from-user', [RoleController::class, 'removeFromUser'])->name('remove');
        });

        // Rutas de eliminación
        Route::middleware('permission:roles,delete')->group(function() {
            Route::delete('{id}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

    // Configuraciones del sistema - Con permisos granulares
    Route::prefix('config')->name('config.')->group(function() {
        // Rutas de visualización
        Route::middleware('permission:configuration,view')->group(function() {
            Route::get('', [ConfigurationController::class, 'index'])->name('index');
            Route::get('export', [ConfigurationController::class, 'export'])->name('export');
        });

        // Rutas de creación
        Route::middleware('permission:configuration,create')->group(function() {
            Route::get('create', [ConfigurationController::class, 'create'])->name('create');
            Route::post('', [ConfigurationController::class, 'store'])->name('store');
            Route::post('import', [ConfigurationController::class, 'import'])->name('import');
        });

        // Rutas de edición
        Route::middleware('permission:configuration,edit')->group(function() {
            Route::get('{id}/edit', [ConfigurationController::class, 'edit'])->name('edit');
            Route::put('{id}', [ConfigurationController::class, 'update'])->name('update');
            Route::post('{id}/reset', [ConfigurationController::class, 'reset'])->name('reset');
        });

        // Rutas de eliminación
        Route::middleware('permission:configuration,delete')->group(function() {
            Route::delete('{id}', [ConfigurationController::class, 'destroy'])->name('destroy');
        });
    });

    // Notificaciones - Con permisos granulares
    Route::prefix('notifications')->name('notifications.')->group(function() {
        // Rutas públicas para todos los usuarios autenticados
        Route::get('', [NotificationController::class, 'index'])->name('index');
        Route::get('unread', [NotificationController::class, 'unread'])->name('unread');
        Route::post('{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');

        // Rutas para administrar notificaciones - Con permisos granulares
        Route::middleware('permission:notifications,view')->group(function() {
            Route::get('templates', [NotificationController::class, 'templates'])->name('templates');
        });

        Route::middleware('permission:notifications,create')->group(function() {
            Route::get('templates/create', [NotificationController::class, 'createTemplate'])->name('templates.create');
            Route::post('templates', [NotificationController::class, 'storeTemplate'])->name('templates.store');
            Route::post('send', [NotificationController::class, 'send'])->name('send');
        });

        Route::middleware('permission:notifications,edit')->group(function() {
            Route::get('templates/{id}/edit', [NotificationController::class, 'editTemplate'])->name('templates.edit');
            Route::put('templates/{id}', [NotificationController::class, 'updateTemplate'])->name('templates.update');
        });

        Route::middleware('permission:notifications,delete')->group(function() {
            Route::delete('templates/{id}', [NotificationController::class, 'destroyTemplate'])->name('templates.destroy');
        });
    });

    // Workflows - Con permisos granulares
    Route::prefix('workflows')->name('workflows.')->group(function() {
        // Rutas de visualización
        Route::middleware('permission:workflows,view')->group(function() {
            Route::get('', [WorkflowController::class, 'index'])->name('index');
            Route::get('{id}', [WorkflowController::class, 'show'])->name('show');
            Route::get('{id}/diagram', [WorkflowController::class, 'diagram'])->name('diagram');
            Route::get('type/{type}', [WorkflowController::class, 'getByType'])->name('by-type');
        });

        // Rutas de creación
        Route::middleware('permission:workflows,create')->group(function() {
            Route::get('create', [WorkflowController::class, 'create'])->name('create');
            Route::post('', [WorkflowController::class, 'store'])->name('store');
        });

        // Rutas de edición
        Route::middleware('permission:workflows,edit')->group(function() {
            Route::get('{id}/edit', [WorkflowController::class, 'edit'])->name('edit');
            Route::put('{id}', [WorkflowController::class, 'update'])->name('update');
        });

        // Rutas de eliminación
        Route::middleware('permission:workflows,delete')->group(function() {
            Route::delete('{id}', [WorkflowController::class, 'destroy'])->name('destroy');
        });
    });

    // Políticas de seguridad - Con permisos granulares
    Route::prefix('security')->name('security.')->group(function() {

        // Rutas de creación
        Route::middleware('permission:security,create')->group(function() {
            Route::get('create', [SecurityPolicyController::class, 'create'])->name('create');
            Route::post('', [SecurityPolicyController::class, 'store'])->name('store');
        });

        // Rutas de visualización
        Route::middleware('permission:security,view')->group(function() {
            Route::get('', [SecurityPolicyController::class, 'index'])->name('index');
            Route::get('{id}', [SecurityPolicyController::class, 'show'])->name('show');
        });

        // Rutas de edición
        Route::middleware('permission:security,edit')->group(function() {
            Route::get('{id}/edit', [SecurityPolicyController::class, 'edit'])->name('edit');
            Route::put('{id}', [SecurityPolicyController::class, 'update'])->name('update');
            Route::post('{id}/activate', [SecurityPolicyController::class, 'activate'])->name('activate');
            Route::post('{id}/deactivate', [SecurityPolicyController::class, 'deactivate'])->name('deactivate');
        });
    });

    // API interna para procesos AJAX
    Route::prefix('api')->name('api.')->group(function() {
        Route::get('user/permissions', [UserController::class, 'getPermissions'])->name('user.permissions');
        Route::get('workflow/states/{workflowId}', [WorkflowController::class, 'getStates'])->name('workflow.states');
        Route::get('workflow/transitions/{stateId}', [WorkflowController::class, 'getTransitions'])->name('workflow.transitions');
        Route::post('workflow/execute-transition', [WorkflowController::class, 'executeTransition'])->name('workflow.execute');
    });
    // Auditoría - Con permisos granulares
    Route::prefix('audit')->name('audit.')->group(function() {
        // Rutas de visualización
        Route::middleware('permission:audit,view')->group(function() {
            Route::get('', [AuditController::class, 'index'])->name('index');
            Route::get('dashboard', [AuditController::class, 'dashboard'])->name('dashboard');
            Route::get('{id}', [AuditController::class, 'show'])->name('show');
            Route::get('export', [AuditController::class, 'export'])->name('export');
        });
    });

});

// API Routes
Route::prefix('api/core')->name('api.core.')->middleware('auth:sanctum')->group(function() {
    // API endpoints para el módulo Core
    Route::apiResource('users', 'UserApiController');
    Route::apiResource('roles', 'RoleApiController');
    Route::apiResource('config', 'ConfigApiController');
    Route::apiResource('workflows', 'WorkflowApiController');

    // Endpoints específicos
    Route::post('users/{id}/roles', 'UserApiController@syncRoles');
    Route::get('users/me', 'UserApiController@currentUser');
    Route::post('notifications/send', 'NotificationApiController@send');
    Route::get('notifications/unread', 'NotificationApiController@unread');
});
