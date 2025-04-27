@extends('core::layouts.master')

@section('title', 'Gestión de Clientes')
@section('page-title', 'Gestión de Clientes')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-users"></i> Lista de Clientes
        </div>
        @can('create-customers')
        <a href="{{ route('customer.customers.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Cliente
        </a>
        @endcan
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="{{ route('customer.customers.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar por nombre, email o documento" name="search" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="business" {{ request('type') == 'business' ? 'selected' : '' }}>Empresa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="segment" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los segmentos</option>
                        @foreach(['residential' => 'Residencial', 'business' => 'Empresarial', 'corporate' => 'Corporativo', 'public' => 'Sector Público'] as $value => $label)
                            <option value="{{ $value }}" {{ request('segment') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('customer.customers.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync me-1"></i> Reiniciar
                    </a>
                </div>
            </div>
        </form>

        <!-- Tabla de clientes -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Segmento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->full_name }}</td>
                        <td>
                            <span class="badge bg-{{ $customer->customer_type == 'individual' ? 'primary' : 'info' }}">
                                {{ $customer->customer_type == 'individual' ? 'Individual' : 'Empresa' }}
                            </span>
                        </td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>
                            @php
                                $segmentClasses = [
                                    'residential' => 'bg-success',
                                    'business' => 'bg-primary',
                                    'corporate' => 'bg-info',
                                    'public' => 'bg-warning'
                                ];
                                $segmentLabels = [
                                    'residential' => 'Residencial',
                                    'business' => 'Empresarial',
                                    'corporate' => 'Corporativo',
                                    'public' => 'Sector Público'
                                ];
                                $segmentClass = $segmentClasses[$customer->segment] ?? 'bg-secondary';
                                $segmentLabel = $segmentLabels[$customer->segment] ?? 'Sin segmento';
                            @endphp
                            <span class="badge {{ $segmentClass }}">{{ $segmentLabel }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->active ? 'success' : 'danger' }}">
                                {{ $customer->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <a href="{{ route('customer.customers.show', $customer->id) }}" class="action-btn" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @can('edit-customers')
                                <a href="{{ route('customer.customers.edit', $customer->id) }}" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                @if($customer->active)
                                    <button type="button" class="action-btn modal-action-btn" 
                                            data-action="deactivate"
                                            data-customer-id="{{ $customer->id }}"
                                            data-customer-name="{{ $customer->full_name }}"
                                            title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="action-btn modal-action-btn" 
                                            data-action="activate"
                                            data-customer-id="{{ $customer->id }}"
                                            data-customer-name="{{ $customer->full_name }}"
                                            title="Activar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @endcan
                                
                                @can('delete-customers')
                                <button type="button" class="action-btn modal-action-btn" 
                                        data-action="delete"
                                        data-customer-id="{{ $customer->id }}"
                                        data-customer-name="{{ $customer->full_name }}"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron clientes con los criterios de búsqueda</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Modales centralizados -->
<!-- Modal para activar cliente -->
@can('edit-customers')
<div class="modal fade" id="activateCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea activar al cliente <strong id="activateCustomerName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="activateCustomerForm" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Activar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para desactivar cliente -->
<div class="modal fade" id="deactivateCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Desactivar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea desactivar al cliente <strong id="deactivateCustomerName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deactivateCustomerForm" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">Desactivar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Modal para eliminar cliente -->
@can('delete-customers')
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar al cliente <strong id="deleteCustomerName"></strong>?</p>
                <p class="text-danger"><strong>Atención:</strong> Esta acción no se puede deshacer y se eliminarán todos los registros relacionados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteCustomerForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración de modales
        const modalActionButtons = document.querySelectorAll('.modal-action-btn');
        
        modalActionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const action = this.getAttribute('data-action');
                const customerId = this.getAttribute('data-customer-id');
                const customerName = this.getAttribute('data-customer-name');
                
                // Configurar el modal según la acción
                if (action === 'activate') {
                    document.getElementById('activateCustomerName').textContent = customerName;
                    document.getElementById('activateCustomerForm').action = `{{ route('customer.customers.activate', '') }}/${customerId}`;
                    
                    const modal = new bootstrap.Modal(document.getElementById('activateCustomerModal'));
                    modal.show();
                }
                else if (action === 'deactivate') {
                    document.getElementById('deactivateCustomerName').textContent = customerName;
                    document.getElementById('deactivateCustomerForm').action = `{{ route('customer.customers.deactivate', '') }}/${customerId}`;
                    
                    const modal = new bootstrap.Modal(document.getElementById('deactivateCustomerModal'));
                    modal.show();
                }
                else if (action === 'delete') {
                    document.getElementById('deleteCustomerName').textContent = customerName;
                    document.getElementById('deleteCustomerForm').action = `{{ route('customer.customers.destroy', '') }}/${customerId}`;
                    
                    const modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
                    modal.show();
                }
            });
        });
    });
</script>
@endpush
@endsection