@extends('core::layouts.master')

@section('title', 'Documentos')
@section('page-title', 'Gestión de Documentos')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-file-alt"></i> Documentos
        </div>
        @can('create-customers')
        <a href="{{ route('customer.documents.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Documento
        </a>
        @endcan
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="{{ route('customer.documents.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="customer_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verificado</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('customer.documents.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync me-1"></i> Reiniciar
                    </a>
                </div>
            </div>
        </form>

        <!-- Tabla de documentos -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                    <tr>
                        <td>{{ $document->id }}</td>
                        <td>{{ $document->name }}</td>
                        <td>
                            <a href="{{ route('customer.customers.show', $document->customer_id) }}">
                                {{ $document->customer->full_name }}
                            </a>
                        </td>
                        <td>{{ $document->documentType->name }}</td>
                        <td>{{ $document->upload_date->format('d/m/Y') }}</td>
                        <td>
                            @if($document->status == 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($document->status == 'verified')
                                <span class="badge bg-success">Verificado</span>
                            @elseif($document->status == 'rejected')
                                <span class="badge bg-danger">Rechazado</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <a href="{{ route('customer.documents.show', $document->id) }}" class="action-btn" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="{{ route('customer.documents.download', $document->id) }}" class="action-btn" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                
                                @can('edit-customers')
                                <a href="{{ route('customer.documents.edit', $document->id) }}" class="action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <button type="button" class="action-btn" title="Cambiar Estado" data-bs-toggle="modal" data-bs-target="#statusModal{{ $document->id }}">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                @endcan
                                
                                @can('delete-customers')
                                <button type="button" class="action-btn" title="Eliminar" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $document->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                            
                            <!-- Modal para cambiar estado -->
                            @can('edit-customers')
                            <div class="modal fade" id="statusModal{{ $document->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cambiar Estado del Documento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('customer.documents.change-status', $document->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Estado</label>
                                                    <select name="status" id="status" class="form-select" required>
                                                        <option value="pending" {{ $document->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                                        <option value="verified" {{ $document->status == 'verified' ? 'selected' : '' }}>Verificado</option>
                                                        <option value="rejected" {{ $document->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            
                            <!-- Modal para eliminar documento -->
                            @can('delete-customers')
                            <div class="modal fade" id="deleteModal{{ $document->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Eliminar Documento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Está seguro de que desea eliminar el documento <strong>{{ $document->name }}</strong>?</p>
                                            <p class="text-danger"><strong>Atención:</strong> Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('customer.documents.destroy', $document->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron documentos con los criterios de búsqueda</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $documents->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection