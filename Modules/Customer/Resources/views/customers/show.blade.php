@extends('core::layouts.master')

@section('title', 'Detalle de Cliente')
@section('page-title', 'Detalle de Cliente')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-3">
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">{{ $customer->full_name }}</h1>
            <div class="action-btn-group">
                @can('edit-customers')
                <a href="{{ route('customer.customers.edit', $customer->id) }}" class="action-btn primary" title="Editar Cliente">
                    <i class="fas fa-edit"></i>
                </a>
                @endcan
                <a href="{{ route('customer.customers.index') }}" class="action-btn" title="Volver al Listado">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Personal -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> Información Personal
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">ID Cliente:</th>
                            <td>{{ $customer->id }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>
                                @if($customer->customer_type == 'individual')
                                    <span class="badge bg-primary">Individual</span>
                                @else
                                    <span class="badge bg-info">Empresa</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Nombre Completo:</th>
                            <td>{{ $customer->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Documento:</th>
                            <td>{{ $customer->identity_document ?: 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $customer->email ?: 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>{{ $customer->phone ?: 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Segmento:</th>
                            <td>
                                @if($customer->segment == 'residential')
                                    <span class="badge bg-success">Residencial</span>
                                @elseif($customer->segment == 'business')
                                    <span class="badge bg-primary">Empresarial</span>
                                @elseif($customer->segment == 'corporate')
                                    <span class="badge bg-info">Corporativo</span>
                                @elseif($customer->segment == 'public')
                                    <span class="badge bg-warning">Sector Público</span>
                                @else
                                    <span class="badge bg-secondary">Sin segmento</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if($customer->active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Puntaje Crediticio:</th>
                            <td>{{ $customer->credit_score ?: 'No evaluado' }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de Registro:</th>
                            <td>{{ $customer->registration_date ? $customer->registration_date->format('d/m/Y') : 'No registrado' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Direcciones -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-map-marker-alt"></i> Direcciones
                </div>
                @can('edit-customers')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus"></i> Nueva Dirección
                </button>
                @endcan
            </div>
            <div class="card-body">
                @forelse($customer->addresses as $address)
                    <div class="address-card mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>
                                    @if($address->address_type == 'main')
                                        <span class="badge bg-primary">Principal</span>
                                    @elseif($address->address_type == 'billing')
                                        <span class="badge bg-info">Facturación</span>
                                    @elseif($address->address_type == 'installation')
                                        <span class="badge bg-success">Instalación</span>
                                    @endif
                                    
                                    @if($address->is_primary)
                                        <span class="badge bg-warning">Dirección Primaria</span>
                                    @endif
                                </h6>
                                <p class="mb-1">
                                    {{ $address->street }} {{ $address->number }}
                                    @if($address->floor || $address->apartment)
                                        , Piso {{ $address->floor }}, Apto {{ $address->apartment }}
                                    @endif
                                </p>
                                <p class="mb-1">{{ $address->city }}, {{ $address->state }}</p>
                                <p class="mb-1">{{ $address->postal_code }}, {{ $address->country }}</p>
                                @if($address->coordinates)
                                    <small class="text-muted">
                                        <i class="fas fa-map-pin"></i> {{ $address->coordinates }}
                                    </small>
                                @endif
                            </div>
                            <div>
                                @can('edit-customers')
                                <div class="action-btn-group">
                                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(!$address->is_primary)
                                    <button class="action-btn" data-bs-toggle="modal" data-bs-target="#deleteAddressModal{{ $address->id }}" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal para editar dirección -->
                    @can('edit-customers')
                    <div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Dirección</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('customer.addresses.update', $address->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <!-- Formulario de dirección (similar al de creación) -->
                                        <!-- (Aquí irían los campos del formulario con los valores actuales) -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal para eliminar dirección -->
                    <div class="modal fade" id="deleteAddressModal{{ $address->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Eliminar Dirección</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de que desea eliminar esta dirección?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{ route('customer.addresses.destroy', $address->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan
                @empty
                    <p class="text-center">No hay direcciones registradas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Documentos -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-file-alt"></i> Documentos
                </div>
                @can('edit-customers')
                <a href="{{ route('customer.documents.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Documento
                </a>
                @endcan
            </div>
            <div class="card-body">
                @if($customer->documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->documents as $document)
                                <tr>
                                    <td>{{ $document->name }}</td>
                                    <td>{{ $document->documentType->name }}</td>
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
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center">No hay documentos registrados</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Interacciones -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-comments"></i> Últimas Interacciones
                </div>
                @can('edit-customers')
                <a href="{{ route('customer.interactions.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nueva Interacción
                </a>
                @endcan
            </div>
            <div class="card-body">
                @if($customer->interactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Canal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->interactions->sortByDesc('date')->take(5) as $interaction)
                                <tr>
                                    <td>{{ $interaction->date->format('d/m/Y H:i') }}</td>
                                    <td>{{ $interaction->interaction_type }}</td>
                                    <td>{{ $interaction->channel }}</td>
                                    <td>
                                        <div class="action-btn-group">
                                            <a href="{{ route('customer.interactions.show', $interaction->id) }}" class="action-btn" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('customer.interactions.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                            Ver Todas
                        </a>
                    </div>
                @else
                    <p class="text-center">No hay interacciones registradas</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir dirección -->
@can('edit-customers')
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Nueva Dirección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customer.addresses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <!-- Formulario de dirección (similar al de creación) -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="street" class="form-label">Calle/Avenida <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="street" name="street" required>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="number" class="form-label">Número</label>
                            <input type="text" class="form-control" id="number" name="number">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="floor" class="form-label">Piso</label>
                            <input type="text" class="form-control" id="floor" name="floor">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="apartment" class="form-label">Apartamento</label>
                            <input type="text" class="form-control" id="apartment" name="apartment">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label for="city" class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="state" class="form-label">Estado/Provincia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="postal_code" class="form-label">Código Postal</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="country" class="form-label">País <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="country" name="country" value="México" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="coordinates" class="form-label">Coordenadas GPS</label>
                            <input type="text" class="form-control" id="coordinates" name="coordinates" placeholder="Ej: 19.4326,-99.1332">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="address_type" class="form-label">Tipo de Dirección <span class="text-danger">*</span></label>
                            <select name="address_type" id="address_type" class="form-select" required>
                                <option value="main">Principal</option>
                                <option value="billing">Facturación</option>
                                <option value="installation">Instalación</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" value="1">
                        <label class="form-check-label" for="is_primary">
                            Establecer como dirección primaria
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Dirección</button>
                </div>
            </form>
        </div>
    </div>
@endcan
@endsection