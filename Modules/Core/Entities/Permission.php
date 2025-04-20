<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Permission extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'module',
        'action',
        'allowed',
        'conditions'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allowed' => 'boolean',
        'conditions' => 'array'
    ];

    /**
     * Get the role that owns the permission.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the permission allows access.
     *
     * @param array $context
     * @return bool
     */
    public function allows($context = [])
    {
        // Si no está permitido, directamente retornar false
        if (!$this->allowed) {
            return false;
        }

        // Si no hay condiciones, retornar true (acceso completo)
        if (empty($this->conditions)) {
            return true;
        }

        // Verificar cada condición contra el contexto
        foreach ($this->conditions as $key => $value) {
            if (!isset($context[$key]) || $context[$key] != $value) {
                return false;
            }
        }

        return true;
    }
}