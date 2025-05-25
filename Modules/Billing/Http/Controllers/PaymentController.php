<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Services\PaymentService;
use Modules\Core\Entities\AuditLog;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * PaymentController constructor.
     *
     * @param PaymentRepository $paymentRepository
     * @param InvoiceRepository $invoiceRepository
     * @param PaymentService $paymentService
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository,
        PaymentService $paymentService
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $from = $request->get('from');
        $to = $request->get('to');
        $perPage = $request->get('per_page', 10);

        $query = $this->paymentRepository->query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function($q) use ($search) {
                      $q->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($from) {
            $query->where('payment_date', '>=', $from);
        }

        if ($to) {
            $query->where('payment_date', '<=', $to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage);

        $statuses = [
            'pending' => 'Pendiente',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'refunded' => 'Reembolsado'
        ];

        $paymentMethods = config('billing.payment.methods');

        return view('billing::payments.index', compact(
            'payments',
            'search',
            'status',
            'paymentMethod',
            'from',
            'to',
            'statuses',
            'paymentMethods'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = $this->invoiceRepository->find($invoiceId);
        }

        // Obtener lista de facturas pendientes para el dropdown
        $pendingInvoices = $this->invoiceRepository->getPendingInvoices();

        // Obtener métodos de pago
        $paymentMethods = config('billing.payment.methods');

        // Fecha por defecto (hoy)
        $paymentDate = Carbon::now()->format('Y-m-d');

        return view('billing::payments.create', compact('invoice', 'pendingInvoices', 'paymentMethods', 'paymentDate'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['status'] = 'completed';

        $result = $this->paymentService->registerPayment($data);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('billing.payments.show', $result['payment']->id)
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $payment = $this->paymentRepository->find($id);

        // Cargar relaciones
        $payment->load(['invoice.contract.customer', 'user']);

        // Obtener logs de auditoría relacionados con este pago
        $logs = AuditLog::where('module', 'payments')
            ->where('action_detail', 'like', "%{$payment->invoice->invoice_number}%")
            ->orderBy('action_date', 'desc')
            ->limit(5)
            ->get();

        return view('billing::payments.show', compact('payment', 'logs'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $payment = $this->paymentRepository->find($id);

        // Solo se pueden editar pagos pendientes
        if ($payment->status !== 'pending') {
            return redirect()->route('billing.payments.show', $id)
                ->with('error', 'Solo se pueden editar pagos en estado pendiente.');
        }

        // Cargar relaciones
        $payment->load(['invoice']);

        // Obtener métodos de pago
        $paymentMethods = config('billing.payment.methods');

        return view('billing::payments.edit', compact('payment', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,failed'
        ]);

        $payment = $this->paymentRepository->find($id);

        // Solo se pueden editar pagos pendientes
        if ($payment->status !== 'pending') {
            return redirect()->route('billing.payments.show', $id)
                ->with('error', 'Solo se pueden editar pagos en estado pendiente.');
        }

        $data = $request->all();

        // Guardar datos anteriores para auditoría
        $oldData = $payment->toArray();

        // Actualizar pago
        $this->paymentRepository->update($id, $data);

        // Si el estado cambia a completado, actualizar la factura
        if ($data['status'] === 'completed' && $payment->status !== 'completed') {
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
            $creditNoteAmount = $invoice->creditNotes()->where('status', 'applied')->sum('amount');

            if ($totalPaid + $creditNoteAmount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } else if ($totalPaid + $creditNoteAmount > 0) {
                $invoice->update(['status' => 'partial']);
            }
        }

        // Registrar en log de auditoría
        AuditLog::register(
            auth()->id(),
            'payment_updated',
            'payments',
            "Pago actualizado para factura: {$payment->invoice->invoice_number}",
            request()->ip(),
            $oldData,
            $payment->refresh()->toArray()
        );

        return redirect()->route('billing.payments.show', $id)
            ->with('success', 'Pago actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payment = $this->paymentRepository->find($id);

        // Solo se pueden eliminar pagos pendientes
        if ($payment->status !== 'pending') {
            return redirect()->route('billing.payments.index')
                ->with('error', 'Solo se pueden eliminar pagos en estado pendiente.');
        }

        // Registrar en log de auditoría
        AuditLog::register(
            auth()->id(),
            'payment_deleted',
            'payments',
            "Pago eliminado para factura: {$payment->invoice->invoice_number}",
            request()->ip(),
            $payment->toArray(),
            null
        );

        // Eliminar pago
        $this->paymentRepository->delete($id);

        return redirect()->route('billing.payments.index')
            ->with('success', 'Pago eliminado correctamente.');
    }

    /**
     * Void the specified payment.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function void(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $result = $this->paymentService->voidPayment($id, $request->reason);

        if (!$result['success']) {
            return redirect()->route('billing.payments.show', $id)
                ->with('error', $result['message']);
        }

        return redirect()->route('billing.payments.show', $id)
            ->with('success', 'Pago anulado correctamente.');
    }

    /**
     * Print payment receipt.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function printReceipt($id)
    {
        $payment = $this->paymentRepository->find($id);

        // Cargar relaciones
        $invoice = $payment->invoice;
        $payment->load(['invoice.contract.customer', 'user']);

        // Aquí implementarías la generación del PDF con alguna librería como DOMPDF
        // Por ahora, solo mostraremos una vista simplificada

        return view('billing::payments.print', compact('payment', 'invoice'));
    }

    /**
     * Display payment report.
     * @param Request $request
     * @return Renderable
     */
    public function report(Request $request)
    {
        $from = $request->get('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::now()->format('Y-m-d'));
        $paymentMethod = $request->get('payment_method');
        $customerId = $request->get('customer_id');

        $filters = [
            'payment_method' => $paymentMethod,
            'customer_id' => $customerId
        ];

        $report = $this->paymentService->getPaymentReport($from, $to, $filters);

        $paymentMethods = config('billing.payment.methods');

        return view('billing::payments.report', compact('report', 'from', 'to', 'paymentMethod', 'customerId', 'paymentMethods'));
    }
}
