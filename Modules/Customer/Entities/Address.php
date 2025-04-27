<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Address extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'address_type',
        'street',
        'number',
        'floor',
        'apartment',
        'city',
        'state',
        'postal_code',
        'country',
        'coordinates',
        'is_primary'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean'
    ];

    /**
     * Get the customer that owns the address.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the full address as a string.
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $parts = [$this->street];
        
        if ($this->number) {
            $parts[] = $this->number;
        }
        
        if ($this->floor && $this->apartment) {
            $parts[] = "Piso {$this->floor}, Depto {$this->apartment}";
        } elseif ($this->floor) {
            $parts[] = "Piso {$this->floor}";
        } elseif ($this->apartment) {
            $parts[] = "Depto {$this->apartment}";
        }
        
        $parts[] = "{$this->city}, {$this->state}";
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
        
        $parts[] = $this->country;
        
        return implode(', ', $parts);
    }

    /**
     * Set the coordinates attribute.
     *
     * @param  array  $value
     * @return void
     */
    public function setCoordinatesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['coordinates'] = implode(',', $value);
        } else {
            $this->attributes['coordinates'] = $value;
        }
    }

    /**
     * Get the coordinates as an array.
     *
     * @return array|null
     */
    public function getCoordinatesArrayAttribute()
    {
        if (!$this->coordinates) {
            return null;
        }
        
        $parts = explode(',', $this->coordinates);
        
        if (count($parts) !== 2) {
            return null;
        }
        
        return [
            'latitude' => trim($parts[0]),
            'longitude' => trim($parts[1])
        ];
    }

    /**
     * Scope a query to only include addresses of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('address_type', $type);
    }

    /**
     * Scope a query to only include primary addresses.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}