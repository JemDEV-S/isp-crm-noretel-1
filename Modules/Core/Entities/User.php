<?php

namespace Modules\Core\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Core\Services\PermissionService;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'username',
        'email',
        'password',
        'status',
        'requires_2fa',
        'last_access',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_access' => 'datetime',
        'preferences' => 'array',
        'requires_2fa' => 'boolean'
    ];

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        return $this->roles->pluck('name')->intersect($roles)->count() > 0;
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function hasPermission($permission, $module, $context = [])
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->hasPermission($this->id, $permission, $module, $context);
    }

    /**
     * Check if the user can view a module.
     *
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canViewModule($module, $context = [])
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->canViewModule($this->id, $module, $context);
    }

    /**
     * Check if the user can create in a module.
     *
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canCreateInModule($module, $context = [])
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->canCreateInModule($this->id, $module, $context);
    }

    /**
     * Check if the user can edit in a module.
     *
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canEditInModule($module, $context = [])
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->canEditInModule($this->id, $module, $context);
    }

    /**
     * Check if the user can delete in a module.
     *
     * @param string $module
     * @param array $context
     * @return bool
     */
    public function canDeleteInModule($module, $context = [])
    {
        $permissionService = app(PermissionService::class);
        return $permissionService->canDeleteInModule($this->id, $module, $context);
    }
}
