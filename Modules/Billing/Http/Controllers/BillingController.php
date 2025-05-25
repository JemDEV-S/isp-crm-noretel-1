<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\PaymentRepository;
use Carbon\Carbon;

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
     * Display a dashboard for billing module.
     * @return Renderable
     */
    public function dashboard(Request $request)
    {
        // Resumen de facturas
        $pendingInvoices = $this->invoiceRepository->getPendingInvoices();
        $overdueInvoices = $this->invoiceRepository->getOverdueInvoices();

        // Totales
        $pendingTotal = $pendingInvoices->sum('total_amount');
        $overdueTotal = $overdueInvoices->sum('total_amount');

        // Pagos recientes
        $recentPayments = $this->paymentRepository->query()
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        // Estadísticas mensuales
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');

        $monthlyInvoices = $this->invoiceRepository->query()
            ->whereMonth('issue_date', $currentMonth)
            ->whereYear('issue_date', $currentYear)
            ->get();

        $monthlyPayments = $this->paymentRepository->query()
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->where('status', 'completed')
            ->get();

        $monthlyInvoicesTotal = $monthlyInvoices->sum('total_amount');
        $monthlyPaymentsTotal = $monthlyPayments->sum('amount');

        // Facturas por estado
        $invoicesByStatus = $this->invoiceRepository->query()
            ->selectRaw('status, count(*) as count, sum(total_amount) as total')
            ->groupBy('status')
            ->get();

        $monthlyComparison = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('M Y');

            // Facturas del mes
            $monthlyInvoicesAmount = $this->invoiceRepository->query()
                ->whereMonth('issue_date', $date->month)
                ->whereYear('issue_date', $date->year)
                ->sum('total_amount');

            // Pagos del mes
            $monthlyPaymentsAmount = $this->paymentRepository->query()
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->where('status', 'completed')
                ->sum('amount');

            $monthlyComparison[$monthKey] = [
                'invoiced' => (float) $monthlyInvoicesAmount,
                'collected' => (float) $monthlyPaymentsAmount
            ];
        }
        return view('billing::dashboard', compact(
            'pendingInvoices',
            'overdueInvoices',
            'pendingTotal',
            'overdueTotal',
            'recentPayments',
            'monthlyInvoicesTotal',
            'monthlyPaymentsTotal',
            'invoicesByStatus',
            'monthlyComparison'
        ));
    }

    /**
     * Display a customer billing summary.
     * @param int $customerId
     * @return Renderable
     */
    public function customerBillingSummary($customerId)
    {
        // Invoices for the customer
        $invoices = $this->invoiceRepository->getInvoicesByCustomer($customerId);

        // Payments for the customer
        $payments = $this->paymentRepository->getPaymentsByCustomer($customerId);

        // Summary stats
        $pendingTotal = $invoices->whereIn('status', ['pending', 'partial'])->sum('total_amount') -
                        $payments->where('status', 'completed')->sum('amount');

        $paidTotal = $payments->where('status', 'completed')->sum('amount');

        $overdueInvoices = $invoices->where('status', 'pending')
            ->where('due_date', '<', Carbon::now())
            ->sortBy('due_date');

        return view('billing::customer_summary', compact(
            'invoices',
            'payments',
            'pendingTotal',
            'paidTotal',
            'overdueInvoices',
            'customerId'
        ));
    }

    /**
     * Display financial reports
     * @param Request $request
     * @return Renderable
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'payments');

        $data = [];

        if ($reportType === 'payments') {
            // Reporte de pagos
            $payments = $this->paymentRepository->query()
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->get();

            // Agrupar por método de pago
            $byMethod = $payments->groupBy('payment_method')
                ->map(function ($items, $key) {
                    return [
                        'method' => $key,
                        'count' => $items->count(),
                        'amount' => $items->sum('amount')
                    ];
                })->values();

            // Agrupar por día
            $byDate = $payments->groupBy(function ($item) {
                return Carbon::parse($item->payment_date)->format('Y-m-d');
            })
            ->map(function ($items, $key) {
                return [
                    'date' => $key,
                    'count' => $items->count(),
                    'amount' => $items->sum('amount')
                ];
            })->values();

            $data = [
                'payments' => $payments,
                'by_method' => $byMethod,
                'by_date' => $byDate,
                'total' => $payments->sum('amount')
            ];
        } elseif ($reportType === 'invoices') {
            // Reporte de facturas
            $invoices = $this->invoiceRepository->query()
                ->whereBetween('issue_date', [$startDate, $endDate])
                ->get();

            // Agrupar por estado
            $byStatus = $invoices->groupBy('status')
                ->map(function ($items, $key) {
                    return [
                        'status' => $key,
                        'count' => $items->count(),
                        'amount' => $items->sum('total_amount')
                    ];
                })->values();

            // Agrupar por día
            $byDate = $invoices->groupBy(function ($item) {
                return Carbon::parse($item->issue_date)->format('Y-m-d');
            })
            ->map(function ($items, $key) {
                return [
                    'date' => $key,
                    'count' => $items->count(),
                    'amount' => $items->sum('total_amount')
                ];
            })->values();

            $data = [
                'invoices' => $invoices,
                'by_status' => $byStatus,
                'by_date' => $byDate,
                'total' => $invoices->sum('total_amount')
            ];
        } elseif ($reportType === 'aging') {
            // Reporte de envejecimiento de cartera
            $overdueInvoices = $this->invoiceRepository->query()
                ->where('status', 'pending')
                ->where('due_date', '<', Carbon::now())
                ->get();

            // Agrupar por rango de días vencidos
            $aging = [
                '1-15' => ['count' => 0, 'amount' => 0],
                '16-30' => ['count' => 0, 'amount' => 0],
                '31-60' => ['count' => 0, 'amount' => 0],
                '61-90' => ['count' => 0, 'amount' => 0],
                '90+' => ['count' => 0, 'amount' => 0],
            ];

            foreach ($overdueInvoices as $invoice) {
                $daysOverdue = now()->diffInDays($invoice->due_date);

                if ($daysOverdue <= 15) {
                    $aging['1-15']['count']++;
                    $aging['1-15']['amount'] += $invoice->pending_amount;
                } elseif ($daysOverdue <= 30) {
                    $aging['16-30']['count']++;
                    $aging['16-30']['amount'] += $invoice->pending_amount;
                } elseif ($daysOverdue <= 60) {
                    $aging['31-60']['count']++;
                    $aging['31-60']['amount'] += $invoice->pending_amount;
                } elseif ($daysOverdue <= 90) {
                    $aging['61-90']['count']++;
                    $aging['61-90']['amount'] += $invoice->pending_amount;
                } else {
                    $aging['90+']['count']++;
                    $aging['90+']['amount'] += $invoice->pending_amount;
                }
            }

            // Agrupar por cliente
            $byCustomer = $overdueInvoices->groupBy(function ($item) {
                return $item->contract->customer_id;
            })
            ->map(function ($items, $customerId) {
                $customer = $items->first()->contract->customer;
                return [
                    'customer_id' => $customerId,
                    'customer_name' => $customer->full_name,
                    'count' => $items->count(),
                    'amount' => $items->sum('pending_amount')
                ];
            })->values()->sortByDesc('amount');

            $data = [
                'overdueInvoices' => $overdueInvoices,
                'aging' => $aging,
                'by_customer' => $byCustomer,
                'total' => $overdueInvoices->sum('pending_amount')
            ];
        }

        return view('billing::reports', compact('data', 'reportType', 'startDate', 'endDate'));
    }
}
