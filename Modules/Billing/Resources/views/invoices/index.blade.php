@extends('core::layouts.master')

@section('title', 'Facturas')
@section('page-title', 'Gestión de Facturas')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice text-primary me-2"></i> Listado de Facturas
            </h5>
            @can('create-invoices')
            <a href="{{ route('billing.invoices.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nueva Factura
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="{{ route('billing.invoices.index') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Nro. factura, cliente...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        @foreach($statuses as $key => $value)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ request('to') }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('billing.invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabla de facturas -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nro. Factura</th>
                        <th>Cliente</th>
                        <th>Emisión</th>
                        <th>Vencimiento</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->contract->customer->full_name }}</td>
                        <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                        <td>
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->status == 'pending' && $invoice->isOverdue())
                                <span class="badge bg-danger ms-1">{{ $invoice->daysOverdue() }} días</span>
                            @endif
                        </td>
                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @if($invoice->status == 'paid')
                                <span class="badge bg-success">Pagada</span>
                            @elseif($invoice->status == 'partial')
                                <span class="badge bg-info">Pago Parcial</span>
                            @elseif($invoice->status == 'pending' && $invoice->isOverdue())
                                <span class="badge bg-danger">Vencida</span>
                            @elseif($invoice->status == 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($invoice->status == 'draft')
                                <span class="badge bg-secondary">Borrador</span>
                            @elseif($invoice->status == 'void')
                                <span class="badge bg-secondary">Anulada</span>
                            @else
                                <span class="badge bg-dark">{{ $invoice->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <a href="{{ route('billing.invoices.show', $invoice->id) }}" class="action-btn" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($invoice->status == 'draft')
                                    <a href="{{ route('billing.invoices.edit', $invoice->id) }}" class="action-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if(in_array($invoice->status, ['pending', 'partial']))
                                    <a href="{{ route('billing.payments.create', ['invoice_id' => $invoice->id]) }}" class="action-btn primary" title="Registrar pago">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                @endif
                                @if($invoice->status == 'draft')
                                    <form action="{{ route('billing.invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Eliminar"
                                                onclick="return confirm('¿Está seguro de eliminar esta factura?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <p class="text-muted mb-0">No se encontraron facturas.</p>
                            @can('create-invoices')
                            <a href="{{ route('billing.invoices.create') }}" class="btn btn-sm btn-primary mt-3">
                                <i class="fas fa-plus me-1"></i> Crear factura
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $invoices->appends(request()->all())->links() }}
        </div>
    </div>
</div>
@endsection
