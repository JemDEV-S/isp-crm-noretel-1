@extends('core::layouts.master')

@section('title', 'Crear Factura')
@section('page-title', 'Crear Factura')

@section('content')
<form action="{{ route('billing.invoices.store') }}" method="POST" id="invoice-form">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <!-- Detalles de Factura -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice text-primary me-2"></i> Detalles de Factura
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contract_id" class="form-label">Contrato <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_id" name="contract_id" required>
                                <option value="">Seleccionar contrato</option>
                                @foreach($contracts as $contract)
                                <option value="{{ $contract->id }}" data-customer="{{ $contract->customer->toJson() }}">
                                    #{{ $contract->id }} - {{ $contract->customer->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="invoice_number" class="form-label">Número de Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $invoiceNumber }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="issue_date" class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ $issueDate }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">Fecha de Vencimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $dueDate }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="billing_period" class="form-label">Período de Facturación</label>
                            <input type="text" class="form-control" id="billing_period" name="billing_period" placeholder="Ej: Mayo 2023">
                        </div>
                        <div class="col-md-6">
                            <label for="generation_type" class="form-label">Tipo de Generación</label>
                            <select class="form-select" id="generation_type" name="generation_type">
                                <option value="manual">Manual</option>
                                <option value="automatic">Automática</option>
                                <option value="recurring">Recurrente</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Cliente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary me-2"></i> Información de Facturación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="billing_name" class="form-label">Nombre/Razón Social</label>
                            <input type="text" class="form-control" id="billing_name" name="billing_name">
                        </div>
                        <div class="col-md-6">
                            <label for="billing_document" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="billing_document" name="billing_document">
                        </div>
                        <div class="col-md-6">
                            <label for="billing_address" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="billing_address" name="billing_address">
                        </div>
                        <div class="col-md-6">
                            <label for="billing_email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="billing_email" name="billing_email">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items de Factura -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list text-primary me-2"></i> Items
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" id="add-item-btn">
                            <i class="fas fa-plus me-1"></i> Agregar Item
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Descripción</th>
                                    <th style="width: 10%;">Cantidad</th>
                                    <th style="width: 15%;">Precio Unit.</th>
                                    <th style="width: 10%;">% Imp.</th>
                                    <th style="width: 15%;">Importe</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí se agregarán los items dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Subtotal:</th>
                                    <th class="text-end" id="subtotal">0.00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Impuestos:</th>
                                    <th class="text-end" id="tax-total">0.00</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-end" id="grand-total">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mensaje de error para items -->
                    <div id="items-error" class="text-danger d-none mt-2">
                        Debe agregar al menos un item a la factura.
                    </div>
                </div>
            </div>

            <!-- Notas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note text-primary me-2"></i> Notas Adicionales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Acciones -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-save text-primary me-2"></i> Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Factura
                        </button>
                        <a href="{{ route('billing.invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-eye text-primary me-2"></i> Vista Previa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h4 id="preview-invoice-number">{{ $invoiceNumber }}</h4>
                        <div id="preview-status" class="badge bg-secondary">Borrador</div>
                    </div>
                    <div class="row">
                        <div class="col-6 text-muted">Cliente:</div>
                        <div class="col-6 text-end" id="preview-customer">-</div>

                        <div class="col-6 text-muted">Fecha:</div>
                        <div class="col-6 text-end" id="preview-date">{{ $issueDate }}</div>

                        <div class="col-6 text-muted">Vencimiento:</div>
                        <div class="col-6 text-end" id="preview-due-date">{{ $dueDate }}</div>

                        <div class="col-6 text-muted">Total:</div>
                        <div class="col-6 text-end" id="preview-total">0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template para items de factura -->
<template id="item-template">
    <tr class="item-row">
        <td>
            <input type="text" class="form-control item-description" name="items[{index}][description]" required>
            <input type="hidden" name="items[{index}][item_type]" value="manual">
            <input type="hidden" name="items[{index}][service_id]" value="">
        </td>
        <td>
            <input type="number" class="form-control item-quantity" name="items[{index}][quantity]" value="1" min="1" step="1" required>
        </td>
        <td>
            <input type="number" class="form-control item-price" name="items[{index}][unit_price]" value="0.00" min="0" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control item-tax-rate" name="items[{index}][tax_rate]" value="18" min="0" step="0.01" required>
            <input type="hidden" class="item-tax-amount" name="items[{index}][tax_amount]" value="0.00">
        </td>
        <td>
            <input type="number" class="form-control item-amount" name="items[{index}][amount]" value="0.00" readonly>
            <input type="hidden" class="item-discount" name="items[{index}][discount]" value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables
    let itemCount = 0;
    const itemTemplate = document.getElementById('item-template').innerHTML;

    // Agregar item
    document.getElementById('add-item-btn').addEventListener('click', function() {
        addItem();
    });

    // Eliminar item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn') || e.target.closest('.remove-item-btn')) {
            const row = e.target.closest('.item-row');
            row.remove();
            updateTotals();

            // Mostrar mensaje si no hay items
            toggleErrorMessage();
        }
    });

    // Actualizar cálculos cuando cambian los inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') ||
            e.target.classList.contains('item-price') ||
            e.target.classList.contains('item-tax-rate')) {

            const row = e.target.closest('.item-row');
            updateRowCalculations(row);
            updateTotals();
        }
    });

    // Actualizar información de cliente al seleccionar contrato
    document.getElementById('contract_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const customerData = JSON.parse(selectedOption.dataset.customer);
            document.getElementById('billing_name').value = customerData.full_name;
            document.getElementById('billing_document').value = customerData.identity_document || '';
            document.getElementById('billing_email').value = customerData.email || '';

            // Buscar la dirección principal
            let address = '';
            if (customerData.primary_address) {
                address = customerData.primary_address;
            }
            document.getElementById('billing_address').value = address;

            // Actualizar vista previa
            document.getElementById('preview-customer').textContent = customerData.full_name;
        } else {
            // Limpiar campos
            document.getElementById('billing_name').value = '';
            document.getElementById('billing_document').value = '';
            document.getElementById('billing_address').value = '';
            document.getElementById('billing_email').value = '';

            // Actualizar vista previa
            document.getElementById('preview-customer').textContent = '-';
        }
    });

    // Actualizar fechas en vista previa
    document.getElementById('issue_date').addEventListener('change', function() {
        document.getElementById('preview-date').textContent = this.value;
    });

    document.getElementById('due_date').addEventListener('change', function() {
        document.getElementById('preview-due-date').textContent = this.value;
    });

    // Actualizar número de factura en vista previa
    document.getElementById('invoice_number').addEventListener('input', function() {
        document.getElementById('preview-invoice-number').textContent = this.value;
    });

    // Validar formulario antes de enviar
    document.getElementById('invoice-form').addEventListener('submit', function(e) {
        const itemRows = document.querySelectorAll('.item-row');

        if (itemRows.length === 0) {
            e.preventDefault();
            document.getElementById('items-error').classList.remove('d-none');
            return false;
        }

        return true;
    });

    // Funciones
    function addItem() {
        const tbody = document.querySelector('#items-table tbody');
        let newRow = itemTemplate.replace(/{index}/g, itemCount);

        tbody.insertAdjacentHTML('beforeend', newRow);

        // Inicializar cálculos
        const row = tbody.lastElementChild;
        updateRowCalculations(row);

        // Incrementar contador
        itemCount++;

        // Ocultar mensaje de error
        document.getElementById('items-error').classList.add('d-none');

        // Actualizar totales
        updateTotals();
    }

    function updateRowCalculations(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;
        const taxRate = parseFloat(row.querySelector('.item-tax-rate').value) || 0;
        const discount = parseFloat(row.querySelector('.item-discount').value) || 0;

        // Calcular subtotal (quantity * unitPrice - discount)
        const subtotal = (quantity * unitPrice) - discount;

        // Calcular impuesto
        const taxAmount = (subtotal * taxRate) / 100;

        // Calcular total
        const total = subtotal + taxAmount;

        // Actualizar campos
        row.querySelector('.item-tax-amount').value = taxAmount.toFixed(2);
        row.querySelector('.item-amount').value = total.toFixed(2);
    }

    function updateTotals() {
        let subtotal = 0;
        let taxTotal = 0;
        let grandTotal = 0;

        // Sumar todos los items
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;
            const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
            const taxAmount = parseFloat(row.querySelector('.item-tax-amount').value) || 0;

            subtotal += (quantity * unitPrice) - discount;
            taxTotal += taxAmount;
            grandTotal += parseFloat(row.querySelector('.item-amount').value) || 0;
        });

        // Actualizar totales en la tabla
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('tax-total').textContent = taxTotal.toFixed(2);
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);

        // Actualizar vista previa
        document.getElementById('preview-total').textContent = grandTotal.toFixed(2);
    }

    function toggleErrorMessage() {
        const itemRows = document.querySelectorAll('.item-row');
        if (itemRows.length === 0) {
            document.getElementById('items-error').classList.remove('d-none');
        } else {
            document.getElementById('items-error').classList.add('d-none');
        }
    }

    // Agregar un item por defecto
    addItem();
});
</script>
@endpush
