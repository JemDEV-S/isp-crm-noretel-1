<?php

namespace Modules\Billing\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Billing\Entities\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceRepository extends BaseRepository
{
    /**
     * InvoiceRepository constructor.
     *
     * @param Invoice $model
     */
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /**
     * Get invoice with related entities.
     *
     * @param int $id
     * @return Invoice
     */
    public function getWithRelations($id)
    {
        return $this->model->with([
            'contract.customer',
            'payments'
        ])->findOrFail($id);
    }

    /**
     * Find invoices by contract id.
     *
     * @param int $contractId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByContract($contractId)
    {
        return $this->model->where('contract_id', $contractId)
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    /**
     * Get invoices by status.
     *
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStatus($status)
    {
        return $this->model->where('status', $status)
            ->with('contract.customer')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get overdue invoices.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverdueInvoices()
    {
        return $this->model->where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('contract.customer')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get pending invoices.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingInvoices()
    {
        return $this->model->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->with('contract.customer')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get invoices by due date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDueDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('due_date', [$startDate, $endDate])
            ->with('contract.customer')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Create invoice with unique invoice number.
     *
     * @param array $data
     * @return Invoice
     */
    public function createWithNumber(array $data)
    {
        // Generate invoice number if not provided
        if (!isset($data['invoice_number'])) {
            $lastInvoice = $this->model->orderBy('id', 'desc')->first();
            $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
            $year = date('Y');
            $month = date('m');
            $data['invoice_number'] = "INV-{$year}{$month}-" . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }
        
        return $this->create($data);
    }

    /**
     * Mark invoice as paid.
     *
     * @param int $id
     * @return bool
     */
    public function markAsPaid($id)
    {
        $invoice = $this->find($id);
        
        return $invoice->update([
            'status' => 'paid'
        ]);
    }

    /**
     * Mark invoice as cancelled.
     *
     * @param int $id
     * @return bool
     */
    public function markAsCancelled($id)
    {
        $invoice = $this->find($id);
        
        return $invoice->update([
            'status' => 'cancelled'
        ]);
    }
}