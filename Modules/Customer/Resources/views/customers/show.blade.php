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
                                    <button class="action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editAddressModal{{ $address->id }}" 
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(!$address->is_primary)
                                    <form action="{{ route('customer.addresses.destroy', $address->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar esta dirección?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label for="street_{{ $address->id }}" class="form-label">Calle/Avenida <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="street_{{ $address->id }}" name="street" value="{{ $address->street }}" required>
                                            </div>
                                            
                                            <div class="col-md-2 mb-3">
                                                <label for="number_{{ $address->id }}" class="form-label">Número</label>
                                                <input type="text" class="form-control" id="number_{{ $address->id }}" name="number" value="{{ $address->number }}">
                                            </div>
                                            
                                            <div class="col-md-2 mb-3">
                                                <label for="floor_{{ $address->id }}" class="form-label">Piso</label>
                                                <input type="text" class="form-control" id="floor_{{ $address->id }}" name="floor" value="{{ $address->floor }}">
                                            </div>
                                            
                                            <div class="col-md-2 mb-3">
                                                <label for="apartment_{{ $address->id }}" class="form-label">Apartamento</label>
                                                <input type="text" class="form-control" id="apartment_{{ $address->id }}" name="apartment" value="{{ $address->apartment }}">
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-3 mb-3">
                                                <label for="city_{{ $address->id }}" class="form-label">Ciudad <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="city_{{ $address->id }}" name="city" value="{{ $address->city }}" required>
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label for="state_{{ $address->id }}" class="form-label">Estado/Provincia <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="state_{{ $address->id }}" name="state" value="{{ $address->state }}" required>
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label for="postal_code_{{ $address->id }}" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="postal_code_{{ $address->id }}" name="postal_code" value="{{ $address->postal_code }}">
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label for="country_{{ $address->id }}" class="form-label">País <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="country_{{ $address->id }}" name="country" value="{{ $address->country }}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label for="coordinates_{{ $address->id }}" class="form-label">Coordenadas GPS</label>
                                                <input type="text" class="form-control" id="coordinates_{{ $address->id }}" name="coordinates" value="{{ $address->coordinates }}" placeholder="Ej: 19.4326,-99.1332">
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="address_type_{{ $address->id }}" class="form-label">Tipo de Dirección <span class="text-danger">*</span></label>
                                                <select name="address_type" id="address_type_{{ $address->id }}" class="form-select" required>
                                                    <option value="main" {{ $address->address_type == 'main' ? 'selected' : '' }}>Principal</option>
                                                    <option value="billing" {{ $address->address_type == 'billing' ? 'selected' : '' }}>Facturación</option>
                                                    <option value="installation" {{ $address->address_type == 'installation' ? 'selected' : '' }}>Instalación</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_primary_{{ $address->id }}" name="is_primary" value="1" {{ $address->is_primary ? 'checked disabled' : '' }}>
                                            <label class="form-check-label" for="is_primary_{{ $address->id }}">
                                                Establecer como dirección primaria
                                            </label>
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
                @empty
                    <p class="text-center">No hay direcciones registradas</p>
                    @can('edit-customers')
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fas fa-plus"></i> Agregar dirección
                        </button>
                    </div>
                    @endcan
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Contactos de Emergencia -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-phone-alt"></i> Contactos de Emergencia
                </div>
                @can('edit-customers')
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmergencyContactModal">
                    <i class="fas fa-plus"></i> Nuevo Contacto
                </button>
                @endcan
            </div>
            <div class="card-body">
                @forelse($customer->emergencyContacts as $contact)
                    <div class="emergency-contact-card mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>{{ $contact->name }}</h6>
                                <p class="mb-1"><strong>Relación:</strong> {{ $contact->relationship }}</p>
                                <p class="mb-1"><strong>Teléfono:</strong> {{ $contact->phone }}</p>
                                @if($contact->email)
                                    <p class="mb-1"><strong>Email:</strong> {{ $contact->email }}</p>
                                @endif
                            </div>
                            <div>
                                @can('edit-customers')
                                <div class="action-btn-group">
                                    <button class="action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editEmergencyContactModal{{ $contact->id }}" 
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('customer.emergencyContacts.destroy', $contact->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este contacto?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal para editar contacto de emergencia -->
                    @can('edit-customers')
                    <div class="modal fade" id="editEmergencyContactModal{{ $contact->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Contacto de Emergencia</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('customer.emergencyContacts.update', $contact->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name_{{ $contact->id }}" class="form-label">Nombre <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name_{{ $contact->id }}" name="name" value="{{ $contact->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="relationship_{{ $contact->id }}" class="form-label">Relación <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="relationship_{{ $contact->id }}" name="relationship" value="{{ $contact->relationship }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone_{{ $contact->id }}" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="phone_{{ $contact->id }}" name="phone" value="{{ $contact->phone }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email_{{ $contact->id }}" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email_{{ $contact->id }}" name="email" value="{{ $contact->email }}">
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
                @empty
                    <p class="text-center">No hay contactos de emergencia registrados</p>
                    @can('edit-customers')
                    <div class="text-center mt-3">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEmergencyContactModal">
                            <i class="fas fa-plus"></i> Agregar contacto
                        </button>
                    </div>
                    @endcan
                @endforelse
            </div>
        </div>
    </div>

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
</div>

<div class="row">
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
                    <!-- Formulario de dirección -->
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
</div>
@endcan

<!-- Modal para añadir contacto de emergencia -->
@can('edit-customers')
<div class="modal fade" id="addEmergencyContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Contacto de Emergencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customer.emergencyContacts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="relationship" class="form-label">Relación <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="relationship" name="relationship" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== DEBUG: Inicialización de la página de detalle ===');
        
        // Verificar si Bootstrap está disponible
        if (typeof bootstrap !== 'undefined') {
            console.log('DEBUG: Bootstrap está disponible en la página');
        } else {
            console.error('ERROR: Bootstrap no está disponible. Los modales no funcionarán correctamente.');
        }
        
        // Revisar modales
        const modals = [
            '#addAddressModal', 
            '#addEmergencyContactModal'
        ];
        
        modals.forEach(modalId => {
            const modalElement = document.querySelector(modalId);
            if (modalElement) {
                console.log(`DEBUG: Modal ${modalId} encontrado en el DOM`);
                
                // Verificar botones que abren este modal
                const buttons = document.querySelectorAll(`[data-bs-toggle="modal"][data-bs-target="${modalId}"]`);
                console.log(`DEBUG: Se encontraron ${buttons.length} botones para abrir el modal ${modalId}`);
                
                // Agregar listener de diagnóstico al modal
                modalElement.addEventListener('show.bs.modal', function() {
                    console.log(`DEBUG: Evento show.bs.modal disparado para ${modalId}`);
                });
                
                modalElement.addEventListener('shown.bs.modal', function() {
                    console.log(`DEBUG: El modal ${modalId} se ha mostrado completamente`);
                });
                
                buttons.forEach((button, index) => {
                    button.addEventListener('click', function(e) {
                        console.log(`DEBUG: Botón #${index} clickeado para abrir ${modalId}`);
                    });
                });
            } else {
                console.error(`ERROR: Modal ${modalId} no encontrado en el DOM`);
            }
        });
        
        // Revisar formularios
        const forms = document.querySelectorAll('form');
        console.log(`DEBUG: Se encontraron ${forms.length} formularios en la página`);
        
        forms.forEach((form, index) => {
            console.log(`DEBUG: Formulario #${index} - Action: ${form.action}`);
            
            form.addEventListener('submit', function(e) {
                console.log(`DEBUG: Formulario #${index} enviado a ${this.action}`);
            });
        });
        
        console.log('=== DEBUG: Fin de la inicialización de la página de detalle ===');
    });
</script>
@endsection