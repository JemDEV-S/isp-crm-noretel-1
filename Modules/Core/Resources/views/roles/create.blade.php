@extends('core::layouts.master')

@section('title', 'Crear Rol')
@section('page-title', 'Crear Nuevo Rol')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Formulario de Creación de Rol</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('core.roles.store') }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input @error('active') is-invalid @enderror" type="checkbox" role="switch" id="active" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Rol activo</label>
                        @error('active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Permisos</label>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Seleccione los permisos que tendrá este rol. Los usuarios con este rol heredarán todos los permisos seleccionados.
                    </div>
                    
                    <div class="accordion" id="accordionPermissions">
                        @foreach($modules as $moduleKey => $module)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $moduleKey }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $moduleKey }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $moduleKey }}">
                                        <div class="d-flex align-items-center w-100">
                                            <strong>{{ $module['name'] }}</strong>
                                            <div class="ms-auto">
                                                <div class="form-check">
                                                    <input class="form-check-input module-checkbox" type="checkbox" id="module_{{ $moduleKey }}" data-module="{{ $moduleKey }}">
                                                    <label class="form-check-label" for="module_{{ $moduleKey }}">Seleccionar todo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $moduleKey }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $moduleKey }}" data-bs-parent="#accordionPermissions">
                                    <div class="accordion-body">
                                        <div class="row">
                                            @foreach($module['actions'] as $actionKey => $actionName)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $moduleKey }}|{{ $actionKey }}" id="permission_{{ $moduleKey }}_{{ $actionKey }}" data-module="{{ $moduleKey }}" {{ (old('permissions') && in_array("$moduleKey|$actionKey", old('permissions'))) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $moduleKey }}_{{ $actionKey }}">
                                                            {{ $actionName }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('permissions')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('core.roles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Lógica para seleccionar/deseleccionar todos los permisos de un módulo
        $('.module-checkbox').on('change', function() {
            const module = $(this).data('module');
            const isChecked = $(this).prop('checked');
            
            $(`input.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
        });
        
        // Actualizar el estado del checkbox de módulo cuando se cambien los permisos individuales
        $('.permission-checkbox').on('change', function() {
            const module = $(this).data('module');
            const totalPermissions = $(`input.permission-checkbox[data-module="${module}"]`).length;
            const checkedPermissions = $(`input.permission-checkbox[data-module="${module}"]:checked`).length;
            
            $(`#module_${module}`).prop('checked', totalPermissions === checkedPermissions);
        });
        
        // Inicializar el estado de los checkboxes de módulos
        $('.module-checkbox').each(function() {
            const module = $(this).data('module');
            const totalPermissions = $(`input.permission-checkbox[data-module="${module}"]`).length;
            const checkedPermissions = $(`input.permission-checkbox[data-module="${module}"]:checked`).length;
            
            $(this).prop('checked', totalPermissions === checkedPermissions && totalPermissions > 0);
        });
    });
</script>
@endpush