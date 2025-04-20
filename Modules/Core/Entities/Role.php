<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Role extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'active',
        'default_permissions'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'default_permissions' => 'array'
    ];

    /**
     * Get the users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param string $permission
     * @param string $module
     * @return bool
     */
    public function hasPermission($permission, $module)
    {
        return $this->permissions()
            ->where('action', $permission)
            ->where('module', $module)
            ->where('allowed', true)
            ->exists();
    }

    /**
     * Grant a permission to the role.
     *
     * @param string $permission
     * @param string $module
     * @param array $conditions
     * @return Permission
     */
    public function grantPermission($permission, $module, $conditions = null)
    {
        return $this->permissions()->create([
            'action' => $permission,
            'module' => $module,
            'allowed' => true,
            'conditions' => $conditions
        ]);
    }

    /**
     * Revoke a permission from the role.
     *
     * @param string $permission
     * @param string $module
     * @return bool
     */
    public function revokePermission($permission, $module)
    {
        return $this->permissions()
            ->where('action', $permission)
            ->where('module', $module)
            ->delete();
    }
}