@extends('core::layouts.master')

@section('title', 'Crear Cliente')
@section('page-title', 'Crear Nuevo Cliente')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus"></i> Formulario de Nuevo Cliente
    </div>
    
    <div class="card-body">
        <form action="{{ route('customer.customers.store') }}" method="POST">
            @csrf
            
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Información Personal</h5>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <label for="customer_type" class="form-label">Tipo de Cliente <span class="text-danger">*</span></label>
                    <select name="customer_type" id="customer_type" class="form-select @error('customer_type') is-invalid @enderror" required>
                        <option value="">Seleccione un tipo</option>
                        @foreach($customerTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('customer_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('customer_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="last_name" class="form-label">Apellido <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <label for="identity_document" class="form-label">Documento de Identidad</label>
                    <input type="text" class="form-control @error('identity_document') is-invalid @enderror" id="identity_document" name="identity_document" value="{{ old('identity_document') }}">
                    @error('identity_document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <label for="segment" class="form-label">Segmento</label>
                    <select name="segment" id="segment" class="form-select @error('segment') is-invalid @enderror">
                        <option value="">Seleccione un segmento</option>
                        @foreach($segments as $value => $label)
                            <option value="{{ $value }}" {{ old('segment') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('segment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="credit_score" class="form-label">Puntaje Crediticio</label>
                    <input type="number" class="form-control @error('credit_score') is-invalid @enderror" id="credit_score" name="credit_score" value="{{ old('credit_score') }}" min="0" max="1000">
                    @error('credit_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="active" class="form-label">Estado</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Cliente Activo</label>
                    </div>
                    @error('active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-4 mt-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Dirección Principal</h5>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="addresses[0][street]" class="form-label">Calle/Avenida <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('addresses.0.street') is-invalid @enderror" id="addresses[0][street]" name="addresses[0][street]" value="{{ old('addresses.0.street') }}" required>
                    @error('addresses.0.street')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="addresses[0][number]" class="form-label">Número</label>
                    <input type="text" class="form-control @error('addresses.0.number') is-invalid @enderror" id="addresses[0][number]" name="addresses[0][number]" value="{{ old('addresses.0.number') }}">
                    @error('addresses.0.number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="addresses[0][floor]" class="form-label">Piso</label>
                    <input type="text" class="form-control @error('addresses.0.floor') is-invalid @enderror" id="addresses[0][floor]" name="addresses[0][floor]" value="{{ old('addresses.0.floor') }}">
                    @error('addresses.0.floor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="addresses[0][apartment]" class="form-label">Apartamento</label>
                    <input type="text" class="form-control @error('addresses.0.apartment') is-invalid @enderror" id="addresses[0][apartment]" name="addresses[0][apartment]" value="{{ old('addresses.0.apartment') }}">
                    @error('addresses.0.apartment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3 mb-3">
                    <label for="addresses[0][city]" class="form-label">Ciudad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('addresses.0.city') is-invalid @enderror" id="addresses[0][city]" name="addresses[0][city]" value="{{ old('addresses.0.city') }}" required>
                    @error('addresses.0.city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="addresses[0][state]" class="form-label">Estado/Provincia <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('addresses.0.state') is-invalid @enderror" id="addresses[0][state]" name="addresses[0][state]" value="{{ old('addresses.0.state') }}" required>
                    @error('addresses.0.state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="addresses[0][postal_code]" class="form-label">Código Postal</label>
                    <input type="text" class="form-control @error('addresses.0.postal_code') is-invalid @enderror" id="addresses[0][postal_code]" name="addresses[0][postal_code]" value="{{ old('addresses.0.postal_code') }}">
                    @error('addresses.0.postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label for="addresses[0][country]" class="form-label">País <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('addresses.0.country') is-invalid @enderror" id="addresses[0][country]" name="addresses[0][country]" value="{{ old('addresses.0.country', 'México') }}" required>
                    @error('addresses.0.country')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label for="addresses[0][coordinates]" class="form-label">Coordenadas GPS</label>
                    <input type="text" class="form-control @error('addresses.0.coordinates') is-invalid @enderror" id="addresses[0][coordinates]" name="addresses[0][coordinates]" value="{{ old('addresses.0.coordinates') }}" placeholder="Ej: 19.4326,-99.1332">
                    @error('addresses.0.coordinates')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="addresses[0][address_type]" class="form-label">Tipo de Dirección <span class="text-danger">*</span></label>
                    <select name="addresses[0][address_type]" id="addresses[0][address_type]" class="form-select @error('addresses.0.address_type') is-invalid @enderror" required>
                        <option value="main" {{ old('addresses.0.address_type') == 'main' ? 'selected' : '' }}>Principal</option>
                        <option value="billing" {{ old('addresses.0.address_type') == 'billing' ? 'selected' : '' }}>Facturación</option>
                        <option value="installation" {{ old('addresses.0.address_type') == 'installation' ? 'selected' : '' }}>Instalación</option>
                    </select>
                    @error('addresses.0.address_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <input type="hidden" name="addresses[0][is_primary]" value="1">
            
            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('customer.customers.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si es necesario, aquí se pueden añadir validaciones adicionales con JavaScript
        
        // Ejemplo: Mostrar/ocultar campos específicos según el tipo de cliente
        const customerTypeSelect = document.getElementById('customer_type');
        
        customerTypeSelect.addEventListener('change', function() {
            const isBusiness = this.value === 'business';
            
            // Aquí podrías hacer cambios en el formulario según el tipo de cliente
            // Por ejemplo, cambiar etiquetas o mostrar/ocultar campos específicos
        });
    });
</script>
@endpush