<?php

namespace Modules\Billing\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Billing\Entities\Payment;

class PaymentRepository extends BaseRepository
{
    /**
     * PaymentRepository constructor.
     *
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * Find payments by invoice id.
     *
     * @param int $invoiceId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByInvoice($invoiceId)
    {
        return $this->model->where('invoice_id', $invoiceId)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Get payments by method.
     *
     * @param string $method
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByMethod($method)
    {
        return $this->model->where('payment_method', $method)
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Get payments by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('payment_date', [$startDate, $endDate])
            ->with('invoice')
            ->orderBy('payment_date')
            ->get();
    }

    /**
     * Get total payments by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');
    }

    /**
     * Create a payment and update invoice status if fully paid.
     *
     * @param array $data
     * @return Payment
     */
    public function createAndUpdateInvoice(array $data)
    {
        $payment = $this->create($data);
        
        // Update invoice status if fully paid
        $invoice = $payment->invoice;
        $totalPaid = $invoice->payments->sum('amount') + $payment->amount;
        
        if ($totalPaid >= $invoice->amount + $invoice->taxes) {
            $invoice->update(['status' => 'paid']);
        }
        
        return $payment;
    }
}