@extends('core::layouts.master')

@section('title', 'Detalle de Pago')
@section('page-title', 'Detalle de Pago')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Información del Pago -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave text-primary me-2"></i> Pago #{{ $payment->id }}
                        @if($payment->status == 'completed')
                            <span class="badge bg-success ms-2">Completado</span>
                        @elseif($payment->status == 'pending')
                            <span class="badge bg-warning ms-2">Pendiente</span>
                        @elseif($payment->status == 'failed')
                            <span class="badge bg-danger ms-2">Fallido</span>
                        @elseif($payment->status == 'refunded')
                            <span class="badge bg-secondary ms-2">Anulado</span>
                        @endif
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Acciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($payment->status == 'completed')
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.payments.print', $payment->id) }}" target="_blank">
                                    <i class="fas fa-print me-2"></i> Imprimir Recibo
                                </a>
                            </li>
                            @endif
                            @if($payment->status == 'pending')
                            <li>
                                <a class="dropdown-item" href="{{ route('billing.payments.edit', $payment->id) }}">
                                    <i class="fas fa-edit me-2"></i> Editar
                                </a>
                            </li>
                            @endif
                            @if($payment->status == 'completed')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#voidPaymentModal">
                                    <i class="fas fa-ban me-2"></i> Anular pago
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
                        <h6 class="text-uppercase text-muted">Información del Pago</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Monto:</strong></td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha:</strong></td>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Método:</strong></td>
                                <td>{{ $payment->payment_method_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Referencia:</strong></td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                            </tr>
                            @if($payment->transaction_id)
                            <tr>
                                <td><strong>ID Transacción:</strong></td>
                                <td>{{ $payment->transaction_id }}</td>
                            </tr>
                            @endif
                            @if($payment->payment_gateway)
                            <tr>
                                <td><strong>Pasarela:</strong></td>
                                <td>{{ $payment->payment_gateway }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted">Factura Asociada</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Número:</strong></td>
                                <td>
                                    <a href="{{ route('billing.invoices.show', $payment->invoice->id) }}">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>{{ $payment->invoice->contract->customer->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Factura:</strong></td>
                                <td>${{ number_format($payment->invoice->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pagado:</strong></td>
                                <td>${{ number_format($payment->invoice->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pendiente:</strong></td>
                                <td>${{ number_format($payment->invoice->pending_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($payment->notes)
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted">Notas</h6>
                    <p class="text-muted">{{ $payment->notes }}</p>
                </div>
                @endif

                @if($payment->payment_details)
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted">Detalles Adicionales</h6>
                    <pre class="bg-light p-3 rounded">{{ json_encode($payment->payment_details, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Estado del Pago -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-primary me-2"></i> Estado del Pago
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Estado
                        <span>
                            @if($payment->status == 'completed')
                                <span class="badge bg-success">Completado</span>
                            @elseif($payment->status == 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($payment->status == 'failed')
                                <span class="badge bg-danger">Fallido</span>
                            @elseif($payment->status == 'refunded')
                                <span class="badge bg-secondary">Anulado</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Registrado el
                        <span>{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    @if($payment->user)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Registrado por
                        <span>{{ $payment->user->username }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary me-2"></i> Cliente
                </h5>
            </div>
            <div class="card-body">
                <h6>{{ $payment->invoice->contract->customer->full_name }}</h6>
                <p class="mb-1">
                    <i class="fas fa-envelope text-muted me-2"></i> {{ $payment->invoice->contract->customer->email }}<br>
                    <i class="fas fa-phone text-muted me-2"></i> {{ $payment->invoice->contract->customer->phone }}
                </p>
                <hr>
                <div class="d-grid">
                    <a href="{{ route('customer.customers.show', $payment->invoice->contract->customer->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user me-1"></i> Ver Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial -->
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

<!-- Modal para anular pago -->
<div class="modal fade" id="voidPaymentModal" tabindex="-1" aria-labelledby="voidPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.payments.void', $payment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="voidPaymentModalLabel">Anular Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Esta acción no se puede deshacer y afectará el estado de la factura asociada.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Anular Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
