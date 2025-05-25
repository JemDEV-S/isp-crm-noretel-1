<?php

namespace Modules\Billing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Contract\Entities\Contract;
use Modules\Core\Entities\User;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'invoice_number',
        'amount',
        'taxes',
        'total_amount',
        'issue_date',
        'due_date',
        'status',
        'document_type',
        'notes',
        'sent',
        'sent_at',
        'billing_name',
        'billing_address',
        'billing_document',
        'billing_email',
        'payment_reference',
        'services_detail',
        'generation_type',
        'billing_period'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'services_detail' => 'json',
        'amount' => 'decimal:2',
        'taxes' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent' => 'boolean'
    ];

    /**
     * Relación con contrato
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relación con pagos
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relación con ítems de factura
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Relación con notas de crédito
     */
    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Relación con recordatorios de pago
     */
    public function reminders()
    {
        return $this->hasMany(PaymentReminder::class);
    }

    /**
     * Obtener el cliente asociado a través del contrato
     */
    public function customer()
    {
        return $this->contract->customer();
    }

    /**
     * Verificar si la factura está vencida
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && now()->gt($this->due_date);
    }

    /**
     * Calcular cuántos días faltan para vencimiento
     */
    public function daysUntilDue()
    {
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Calcular cuántos días está vencida
     */
    public function daysOverdue()
    {
        if ($this->isOverdue()) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }

    /**
     * Calcular monto pagado
     */
    public function getPaidAmountAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Calcular saldo pendiente
     */
    public function getPendingAmountAttribute()
    {
        $paidAmount = $this->paid_amount;
        $creditNoteAmount = $this->creditNotes()->where('status', 'applied')->sum('amount');
        return $this->total_amount - $paidAmount - $creditNoteAmount;
    }
}
