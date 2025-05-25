@extends('core::layouts.master')

@section('title', 'Detalle de Factura')
@section('page-title', 'Detalle de Factura')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Información de Factura -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice text-primary me-2"></i> Factura {{ $invoice->invoice_number }}
                        @if($invoice->status == 'draft')
                            <span class="badge bg-secondary ms-2">Borrador</span>
                        @elseif($invoice->status == 'pending')
                            @if($invoice->isOverdue())
                                <span class="badge bg-danger ms-2">Vencida</span>
                            @else
                                <span class="badge bg-warning ms-2">Pendiente</span>
                            @endif
                        @elseif($invoice->status == 'partial')
                            <span class="badge bg-info ms-2">Pago Parcial</span>
                        @elseif($invoice->status == 'paid')
                            <span class="badge bg-success ms-2">Pagada</span>
                        @elseif($invoice->status == 'void')
                            <span class="badge bg-secondary ms-2">Anulada</span>
                        @endif
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Acciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.invoices.print', $invoice->id) }}" target="_blank">
                                    <i class="fas fa-print me-2"></i> Imprimir
                                </a>
                            </li>
                            @if(!$invoice->sent)
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('send-invoice-form').submit();">
                                    <i class="fas fa-envelope me-2"></i> Enviar por correo
                                </a>
                                <form id="send-invoice-form" action="{{ route('billing.invoices.email', $invoice->id) }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                            @endif
                            @if($invoice->status == 'draft')
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.invoices.edit', $invoice->id) }}">
                                    <i class="fas fa-edit me-2"></i> Editar
                                </a>
                            </li>
                            @endif
                            @if(in_array($invoice->status, ['pending', 'partial']))
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.payments.create', ['invoice_id' => $invoice->id]) }}">
                                    <i class="fas fa-money-bill-wave me-2"></i> Registrar pago
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.credit-notes.create', ['invoice_id' => $invoice->id]) }}">
                                    <i class="fas fa-receipt me-2"></i> Crear nota de crédito
                                </a>
                            </li>
                            @endif
                            @if(in_array($invoice->status, ['pending', 'partial']))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#voidInvoiceModal">
                                    <i class="fas fa-ban me-2"></i> Anular factura
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted">Factura a</h6>
                        <p class="mb-1"><strong>{{ $invoice->billing_name }}</strong></p>
                        <p class="mb-1">{{ $invoice->billing_address }}</p>
                        <p class="mb-1">{{ $invoice->billing_document }}</p>
                        <p class="mb-0">{{ $invoice->billing_email }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-uppercase text-muted">Detalles</h6>
                        <p class="mb-1"><strong>Número:</strong> {{ $invoice->invoice_number }}</p>
                        <p class="mb-1"><strong>Fecha:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
                        <p class="mb-1"><strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
                        @if($invoice->billing_period)
                        <p class="mb-0"><strong>Período:</strong> {{ $invoice->billing_period }}</p>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped border">
                        <thead class="bg-light">
                            <tr>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Impuesto</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->tax_amount, 2) }} ({{ $item->tax_rate }}%)</td>
                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-end">Subtotal:</th>
                                <th class="text-end">{{ number_format($invoice->amount, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Impuestos:</th>
                                <th class="text-end">{{ number_format($invoice->taxes, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">{{ number_format($invoice->total_amount, 2) }}</th>
                            </tr>
                            @if(in_array($invoice->status, ['partial', 'paid']))
                            <tr>
                                <th colspan="4" class="text-end">Pagado:</th>
                                <th class="text-end">{{ number_format($invoice->paid_amount, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Pendiente:</th>
                                <th class="text-end">{{ number_format($invoice->pending_amount, 2) }}</th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                @if($invoice->notes)
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted">Notas</h6>
                    <p class="text-muted">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Pagos Relacionados -->
        @if(count($invoice->payments) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-money-bill-wave text-primary me-2"></i> Pagos Registrados
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Estado</th>
                                <th class="text-end">Monto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>{{ $payment->payment_method_name }}</td>
                                <td>{{ $payment->reference }}</td>
                                <td>
                                    @if($payment->status == 'completed')
                                        <span class="badge bg-success">Completado</span>
                                    @elseif($payment->status == 'pending')
                                        <span class="badge bg-warning">Pendiente</span>
                                    @elseif($payment->status == 'refunded')
                                        <span class="badge bg-secondary">Anulado</span>
                                    @else
                                        <span class="badge bg-danger">{{ $payment->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('billing.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Notas de Crédito -->
        @if(count($invoice->creditNotes) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-receipt text-primary me-2"></i> Notas de Crédito
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th class="text-end">Monto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->creditNotes as $creditNote)
                            <tr>
                                <td>{{ $creditNote->credit_note_number }}</td>
                                <td>{{ $creditNote->issue_date->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($creditNote->reason, 30) }}</td>
                                <td>
                                    @if($creditNote->status == 'applied')
                                        <span class="badge bg-success">Aplicada</span>
                                    @elseif($creditNote->status == 'active')
                                        <span class="badge bg-info">Activa</span>
                                    @elseif($creditNote->status == 'draft')
                                        <span class="badge bg-secondary">Borrador</span>
                                    @elseif($creditNote->status == 'void')
                                        <span class="badge bg-secondary">Anulada</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($creditNote->amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('billing.credit-notes.show', $creditNote->id) }}" class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Información del Cliente y Contrato -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary me-2"></i> Cliente y Contrato
                </h5>
            </div>
            <div class="card-body">
                <h6>Cliente</h6>
                <p class="mb-1">
                    <a href="{{ route('customer.customers.show', $invoice->contract->customer->id) }}">
                        {{ $invoice->contract->customer->full_name }}
                    </a>
                </p>
                <p class="mb-3">
                    <i class="fas fa-envelope text-muted me-2"></i> {{ $invoice->contract->customer->email }}<br>
                    <i class="fas fa-phone text-muted me-2"></i> {{ $invoice->contract->customer->phone }}
                </p>

                <h6>Contrato</h6>
                <p class="mb-1">
                    <a href="{{ route('contract.contracts.show', $invoice->contract->id) }}">
                        Contrato #{{ $invoice->contract->id }}
                    </a>
                </p>
                <p class="mb-0">
                    <strong>Plan:</strong> {{ $invoice->contract->plan->name }}<br>
                    <strong>Estado:</strong>
                    @if($invoice->contract->status == 'active')
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">{{ $invoice->contract->status }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Estado de Factura -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-primary me-2"></i> Estado de Factura
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Estado
                        <span>
                            @if($invoice->status == 'draft')
                                <span class="badge bg-secondary">Borrador</span>
                            @elseif($invoice->status == 'pending')
                                @if($invoice->isOverdue())
                                    <span class="badge bg-danger">Vencida</span>
                                @else
                                    <span class="badge bg-warning">Pendiente</span>
                                @endif
                            @elseif($invoice->status == 'partial')
                                <span class="badge bg-info">Pago Parcial</span>
                            @elseif($invoice->status == 'paid')
                                <span class="badge bg-success">Pagada</span>
                            @elseif($invoice->status == 'void')
                                <span class="badge bg-secondary">Anulada</span>
                            @endif
                        </span>
                    </li>
                    @if(in_array($invoice->status, ['pending', 'partial']))
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Vencimiento
                        <span>
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->isOverdue())
                                <span class="badge bg-danger ms-1">{{ $invoice->daysOverdue() }} días</span>
                            @else
                                <span class="badge bg-success ms-1">Faltan {{ $invoice->daysUntilDue() }} días</span>
                            @endif
                        </span>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Generada el
                        <span>{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Tipo de generación
                        <span>
                            @if($invoice->generation_type == 'manual')
                                <span class="badge bg-primary">Manual</span>
                            @elseif($invoice->generation_type == 'automatic')
                                <span class="badge bg-info">Automática</span>
                            @elseif($invoice->generation_type == 'recurring')
                                <span class="badge bg-warning">Recurrente</span>
                            @else
                                <span class="badge bg-secondary">{{ $invoice->generation_type }}</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Enviada
                        <span>
                            @if($invoice->sent)
                                <span class="badge bg-success">Sí</span> {{ $invoice->sent_at->format('d/m/Y H:i') }}
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Registros de Auditoría -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary me-2"></i> Historial
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($logs as $log)
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $log->action_type }}</h6>
                            <small>{{ $log->action_date->format('d/m/Y H:i') }}</small>
                        </div>
                        <p class="mb-1">{{ $log->action_detail }}</p>
                        <small>Por: {{ $log->user->username ?? 'Sistema' }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para anular factura -->
<div class="modal fade" id="voidInvoiceModal" tabindex="-1" aria-labelledby="voidInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.invoices.void', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="voidInvoiceModalLabel">Anular Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Esta acción no se puede deshacer.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Anular Factura</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
