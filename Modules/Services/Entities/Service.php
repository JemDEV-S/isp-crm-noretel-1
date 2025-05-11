<?php

namespace Modules\Services\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'service_type',
        'technology',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Los planes asociados a este servicio.
     */
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Los servicios adicionales asociados a este servicio.
     */
    public function additionalServices()
    {
        return $this->hasMany(AdditionalService::class);
    }

    /**
     * Obtener solo servicios activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
