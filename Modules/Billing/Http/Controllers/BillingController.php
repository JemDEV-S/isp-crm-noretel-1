<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Contract\Repositories\ContractRepository;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * BillingController constructor.
     *
     * @param InvoiceRepository $invoiceRepository
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        PaymentRepository $paymentRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Display the billing dashboard.
     * @return Renderable
     */
    public function dashboard()
    {
        // Estadísticas básicas
        $stats = [
            'pending_invoices_count' => $this->invoiceRepository->getByStatus('pending')->count(),
            'overdue_invoices_count' => $this->invoiceRepository->getOverdueInvoices()->count(),
            'paid_invoices_count' => $this->invoiceRepository->getByStatus('paid')->count(),
            'cancelled_invoices_count' => $this->invoiceRepository->getByStatus('cancelled')->count(),
        ];
        
        // Facturación del mes actual
        $currentMonth = date('Y-m-01');
        $nextMonth = date('Y-m-01', strtotime('+1 month'));
        $currentMonthInvoices = $this->invoiceRepository->getByDueDateRange($currentMonth, $nextMonth);
        
        // Pagos del mes actual
        $currentMonthPayments = $this->paymentRepository->getByDateRange($currentMonth, $nextMonth);
        $totalPaymentsCurrentMonth = $this->paymentRepository->getTotalByDateRange($currentMonth, $nextMonth);
        
        // Facturas recientes
        $recentInvoices = $this->invoiceRepository->query()
            ->with('contract.customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Pagos recientes
        $recentPayments = $this->paymentRepository->query()
            ->with('invoice.contract.customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('billing::dashboard', compact(
            'stats',
            'currentMonthInvoices',
            'currentMonthPayments',
            'totalPaymentsCurrentMonth',
            'recentInvoices',
            'recentPayments'
        ));
    }
}