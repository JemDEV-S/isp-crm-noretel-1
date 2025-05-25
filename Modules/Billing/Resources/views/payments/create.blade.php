@extends('core::layouts.master')

@section('title', 'Registrar Pago')
@section('page-title', 'Registrar Pago')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-money-bill-wave text-primary me-2"></i> Datos del Pago
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('billing.payments.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="invoice_id" class="form-label">Factura <span class="text-danger">*</span></label>
                            @if($invoice)
                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                <div class="form-control disabled bg-light">{{ $invoice->invoice_number }}</div>
                            @else
                                <select class="form-select" id="invoice_id" name="invoice_id" required>
                                    <option value="">Seleccionar factura</option>
                                    @foreach($pendingInvoices as $pendingInvoice)
                                    <option value="{{ $pendingInvoice->id }}" data-amount="{{ $pendingInvoice->pending_amount }}" data-customer="{{ $pendingInvoice->contract->customer->full_name }}">
                                        {{ $pendingInvoice->invoice_number }} - {{ $pendingInvoice->contract->customer->full_name }} ($ {{ number_format($pendingInvoice->pending_amount, 2) }})
                                    </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required @if($invoice) value="{{ $invoice->pending_amount }}" @endif>
                            </div>
                            <div id="amount-feedback" class="form-text text-muted">
                                @if($invoice)
                                    Monto pendiente: ${{ number_format($invoice->pending_amount, 2) }}
                                @else
                                    Monto pendiente de la factura
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar método</option>
                                @foreach($paymentMethods as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ $paymentDate }}" required>
                        </div>

                        <div class="col-md-12">
                            <label for="reference" class="form-label">Referencia / Nro. Transacción</label>
                            <input type="text" class="form-control" id="reference" name="reference">
                        </div>

                        <div class="col-md-12">
                            <label for="notes" class="form-label">Notas adicionales</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ $invoice ? route('billing.invoices.show', $invoice->id) : route('billing.payments.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Información de Factura -->
        <div class="card mb-4" id="invoice-info-card" @if(!$invoice) style="display: none;" @endif>
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice text-primary me-2"></i> Información de Factura
                </h5>
            </div>
            <div class="card-body">
                <h6 id="invoice-number">
                    @if($invoice) {{ $invoice->invoice_number }} @else Número de Factura @endif
                </h6>

                <div class="mb-3">
                    <small class="text-muted">Cliente</small>
                    <div id="customer-name">
                        @if($invoice) {{ $invoice->contract->customer->full_name }} @else Nombre del Cliente @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Fecha de Emisión</small>
                        <div id="issue-date">
                            @if($invoice) {{ $invoice->issue_date->format('d/m/Y') }} @else -- @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Fecha de Vencimiento</small>
                        <div id="due-date">
                            @if($invoice) {{ $invoice->due_date->format('d/m/Y') }} @else -- @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Total de Factura</small>
                        <div id="total-amount">
                            @if($invoice) ${{ number_format($invoice->total_amount, 2) }} @else $0.00 @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Monto Pagado</small>
                        <div id="paid-amount">
                            @if($invoice) ${{ number_format($invoice->paid_amount, 2) }} @else $0.00 @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <strong>Pendiente:</strong>
                    <span class="h5 mb-0 text-danger" id="pending-amount">
                        @if($invoice) ${{ number_format($invoice->pending_amount, 2) }} @else $0.00 @endif
                    </span>
                </div>

                @if($invoice && $invoice->status == 'pending' && $invoice->isOverdue())
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i> Factura vencida hace {{ $invoice->daysOverdue() }} días
                </div>
                @endif
            </div>
        </div>

        <!-- Recomendaciones -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-primary me-2"></i> Recomendaciones
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i> Verifique que el monto del pago sea correcto
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i> Asegúrese de seleccionar el método de pago correcto
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i> Ingrese una referencia para facilitar la conciliación
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoice_id');

    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                // Obtener datos de la factura
                const pendingAmount = parseFloat(selectedOption.dataset.amount) || 0;
                const customerName = selectedOption.dataset.customer || '';

                // Actualizar monto sugerido
                document.getElementById('amount').value = pendingAmount.toFixed(2);
                document.getElementById('amount-feedback').textContent = `Monto pendiente: $${pendingAmount.toFixed(2)}`;

                // Mostrar tarjeta de información
                document.getElementById('invoice-info-card').style.display = 'block';

                // Actualizar campos
                document.getElementById('invoice-number').textContent = selectedOption.textContent.split(' - ')[0];
                document.getElementById('customer-name').textContent = customerName;
                document.getElementById('pending-amount').textContent = `$${pendingAmount.toFixed(2)}`;
            } else {
                // Ocultar tarjeta de información
                document.getElementById('invoice-info-card').style.display = 'none';
            }
        });
    }
});
</script>
@endpush
