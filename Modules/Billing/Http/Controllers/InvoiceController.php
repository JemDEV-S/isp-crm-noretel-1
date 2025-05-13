<?php

namespace Modules\Billing\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Contract\Repositories\ContractRepository;
use Modules\Core\Entities\AuditLog;
use Modules\Billing\Http\Requests\StoreInvoiceRequest;
use Modules\Billing\Http\Requests\UpdateInvoiceRequest;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
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
     * @var ContractRepository
     */
    protected $contractRepository;

    /**
     * InvoiceController constructor.
     *
     * @param InvoiceRepository $invoiceRepository
     * @param PaymentRepository $paymentRepository
     * @param ContractRepository $contractRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        PaymentRepository $paymentRepository,
        ContractRepository $contractRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->contractRepository = $contractRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $contractId = $request->get('contract_id');
        $perPage = $request->get('per_page', 10);

        $query = $this->invoiceRepository->query();

        // Apply filters
        if ($search) {
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('contract.customer', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('identity_document', 'like', "%{$search}%");
                });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($contractId) {
            $query->where('contract_id', $contractId);
        }

        // With relationships
        $query->with(['contract.customer']);

        // Order by issue date
        $query->orderBy('issue_date', 'desc');

        $invoices = $query->paginate($perPage);

        return view('billing::invoices.index', compact(
            'invoices', 
            'search', 
            'status', 
            'contractId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $contractId = $request->get('contract_id');
        $contract = null;

        if ($contractId) {
            $contract = $this->contractRepository->find($contractId);
        }

        $activeContracts = $this->contractRepository->query()
            ->where('status', 'active')
            ->with('customer')
            ->get();

        return view('billing::invoices.create', compact(
            'contract',
            'activeContracts'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreInvoiceRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'document_type' => 'nullable|string'
        ]);

        // Preparar datos
        $data = $request->all();
        
        // Generar factura con número único
        $invoice = $this->invoiceRepository->createWithNumber([
            'contract_id' => $data['contract_id'],
            'amount' => $data['amount'],
            'taxes' => $data['taxes'] ?? 0,
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'status' => 'pending',
            'document_type' => $data['document_type'] ?? 'invoice'
        ]);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'invoice_created',
            'invoices',
            "Factura {$invoice->invoice_number} creada para el contrato #{$invoice->contract_id}",
            $request->ip(),
            null,
            $invoice->toArray()
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
            ->with('success', 'Factura creada correctamente.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $invoice = $this->invoiceRepository->getWithRelations($id);
        
        return view('billing::invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $invoice = $this->invoiceRepository->getWithRelations($id);
        
        return view('billing::invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateInvoiceRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validación básica
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'document_type' => 'nullable|string'
        ]);

        $invoice = $this->invoiceRepository->find($id);
        
        // No permitir editar facturas pagadas o canceladas
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'No se pueden editar facturas pagadas o canceladas.');
        }
        
        // Guardar datos antiguos para auditoría
        $oldData = $invoice->toArray();
        
        // Actualizar factura
        $invoice = $this->invoiceRepository->update($id, [
            'amount' => $request->amount,
            'taxes' => $request->taxes ?? 0,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'document_type' => $request->document_type ?? 'invoice'
        ]);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'invoice_updated',
            'invoices',
            "Factura {$invoice->invoice_number} actualizada",
            $request->ip(),
            $oldData,
            $invoice->toArray()
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
            ->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $invoice = $this->invoiceRepository->find($id);
        
        // No permitir eliminar facturas con pagos
        if ($invoice->payments->count() > 0) {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'No se puede eliminar una factura que tiene pagos registrados.');
        }
        
        // Guardar datos para auditoría
        $invoiceData = $invoice->toArray();
        
        // Eliminar factura
        $this->invoiceRepository->delete($id);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'invoice_deleted',
            'invoices',
            "Factura {$invoice->invoice_number} eliminada",
            $request->ip(),
            $invoiceData,
            null
        );
        
        return redirect()->route('billing.invoices.index')
            ->with('success', 'Factura eliminada correctamente.');
    }

    /**
     * Mark invoice as paid.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function markAsPaid($id, Request $request)
    {
        $invoice = $this->invoiceRepository->find($id);
        
        // No permitir marcar como pagada si ya está pagada o cancelada
        if ($invoice->status === 'paid') {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'La factura ya está marcada como pagada.');
        }
        
        if ($invoice->status === 'cancelled') {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'No se puede marcar como pagada una factura cancelada.');
        }
        
        // Guardar datos antiguos para auditoría
        $oldData = $invoice->toArray();
        
        // Marcar como pagada
        $this->invoiceRepository->markAsPaid($id);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'invoice_marked_as_paid',
            'invoices',
            "Factura {$invoice->invoice_number} marcada como pagada",
            $request->ip(),
            $oldData,
            $invoice->fresh()->toArray()
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
            ->with('success', 'Factura marcada como pagada correctamente.');
    }

    /**
     * Mark invoice as cancelled.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function markAsCancelled($id, Request $request)
    {
        $invoice = $this->invoiceRepository->find($id);
        
        // No permitir cancelar si ya está pagada o cancelada
        if ($invoice->status === 'cancelled') {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'La factura ya está cancelada.');
        }
        
        if ($invoice->status === 'paid') {
            return redirect()->route('billing.invoices.show', $invoice->id)
                ->with('error', 'No se puede cancelar una factura pagada.');
        }
        
        // Guardar datos antiguos para auditoría
        $oldData = $invoice->toArray();
        
        // Marcar como cancelada
        $this->invoiceRepository->markAsCancelled($id);
        
        // Registrar acción para auditoría
        AuditLog::register(
            Auth::id(),
            'invoice_cancelled',
            'invoices',
            "Factura {$invoice->invoice_number} cancelada",
            $request->ip(),
            $oldData,
            $invoice->fresh()->toArray()
        );
        
        return redirect()->route('billing.invoices.show', $invoice->id)
            ->with('success', 'Factura cancelada correctamente.');
    }

    /**
     * Display overdue invoices.
     * @return Renderable
     */
    public function overdue()
    {
        $overdue = $this->invoiceRepository->getOverdueInvoices();
        
        return view('billing::invoices.overdue', compact('overdue'));
    }

    /**
     * Display pending invoices.
     * @return Renderable
     */
    public function pending()
    {
        $pending = $this->invoiceRepository->getPendingInvoices();
        
        return view('billing::invoices.pending', compact('pending'));
    }
}