<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Entities\AuditLog;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Customer extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_type',
        'first_name',
        'last_name',
        'identity_document',
        'email',
        'phone',
        'credit_score',
        'contact_preferences',
        'segment',
        'registration_date',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registration_date' => 'datetime',
        'active' => 'boolean',
        'credit_score' => 'integer',
        'contact_preferences' => 'json'
    ];

    /**
     * Get the addresses for the customer.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the emergency contacts for the customer.
     */
    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get the documents for the customer.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the interactions for the customer.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Get the leads associated with the customer.
     */
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'customer_lead')
                    ->withPivot('conversion_date', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the primary address for the customer.
     */
    public function primaryAddress()
    {
        return $this->hasOne(Address::class)->where('is_primary', true);
    }

    /**
     * Get the full name of the customer.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include active customers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include customers of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    /**
     * Scope a query to only include customers in a specific segment.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $segment
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInSegment($query, $segment)
    {
        return $query->where('segment', $segment);
    }

    /**
     * Check if the customer has any active contracts.
     *
     * @return bool
     */
    public function hasActiveContracts()
    {
        // Implementar cuando esté disponible el módulo de contratos
        return false;
    }

    /**
     * Get the customer's contracts.
     * (Esta será implementada cuando el módulo de contratos esté disponible)
     */
    // public function contracts()
    // {
    //     return $this->hasMany(Contract::class);
    // }
}