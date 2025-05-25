<?php

namespace Modules\Billing\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'reminder_date',
        'status',
        'type',
        'message',
        'channel',
        'sent_at'
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'sent_at' => 'datetime'
    ];

    /**
     * Relación con factura
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
