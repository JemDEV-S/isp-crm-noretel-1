<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Core\Entities\AuditLog;
use Modules\Billing\Http\Requests\StorePaymentRequest;
use Illuminate\Support\Facades\Auth;

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
     * PaymentController constructor.
     *
     * @param PaymentRepository $paymentRepository
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $method = $request->get('method');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $perPage = $request->get('per_page', 10);

        $query = $this->paymentRepository->query();

        // Apply filters
        if ($search) {
            $query->whereHas('invoice', function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('contract.customer', function($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        } else if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        } else if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }

        // With relationships
        $query->with(['invoice.contract.customer']);

        // Order by date
        $query->orderBy('payment_date', 'desc');

        $payments = $query->paginate($perPage);

        return view('billing::payments.index', compact(
            'payments', 
            'search', 
            'method', 
            'startDate',
            'endDate'
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
            $invoice = $this->invoiceRepository->getWithRelations($invoiceId);
        }

        $pendingInvoices = $this->invoiceRepository->getByStatus('pending');

        return view('billing::payments.create', compact(
            'invoice',
            'pendingInvoices'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * @param StorePaymentRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string'
        ]);
        
        $invoice = $this->invoiceRepository->find($request->invoice_id);
        
        // Verificar que la factura no esté cancelada o ya pagada
        if ($invoice->status === 'cancelled') {
            return redirect()->back()
                ->with('error', 'No se puede registrar un pago para una factura cancelada.')
                ->withInput();
        }
        
        if ($invoice->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Esta factura ya ha sido pagada completamente.')
                ->withInput();
        }
        
        // Crear pago y actualizar estado de la factura si es necesario
        $payment = $this->paymentRepository->createAndUpdateInvoice([
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'reference' => $request->reference
        ]);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'payment_created',
            'payments',
            "Pago registrado para factura {$invoice->invoice_number}",
            $request->ip(),
            null,
            $payment->toArray()
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
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
        $payment->load('invoice.contract.customer');
        
        return view('billing::payments.show', compact('payment'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $payment = $this->paymentRepository->find($id);
        $invoice = $payment->invoice;
        
        // Guardar datos para auditoría
        $paymentData = $payment->toArray();
        
        // Eliminar pago
        $this->paymentRepository->delete($id);
        
        // Actualizar estado de la factura si es necesario
        if ($invoice->status === 'paid') {
            $totalPaid = $invoice->payments->where('id', '!=', $id)->sum('amount');
            
            if ($totalPaid < $invoice->amount + $invoice->taxes) {
                $invoice->update(['status' => 'pending']);
            }
        }
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'payment_deleted',
            'payments',
            "Pago eliminado para factura {$invoice->invoice_number}",
            $request->ip(),
            $paymentData,
            null
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
            ->with('success', 'Pago eliminado correctamente.');
    }
}