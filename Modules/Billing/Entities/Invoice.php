<?php

namespace Modules\Billing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Contract\Entities\Contract;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Invoice extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contract_id',
        'invoice_number',
        'amount',
        'taxes',
        'issue_date',
        'due_date',
        'status',
        'document_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'taxes' => 'decimal:2',
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Get the contract that owns the invoice.
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the total amount of the invoice.
     */
    public function getTotalAttribute()
    {
        return $this->amount + $this->taxes;
    }

    /**
     * Get the paid amount of the invoice.
     */
    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    /**
     * Get the remaining amount to be paid.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    /**
     * Check if the invoice is fully paid.
     */
    public function getIsPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * Check if the invoice is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && !$this->is_paid;
    }

    /**
     * Calculate the number of days overdue.
     */
    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        
        return $this->due_date->diffInDays(now());
    }
}