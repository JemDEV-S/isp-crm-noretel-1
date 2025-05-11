@extends('services::layouts.master')

@section('title', isset($service) ? 'Servicios Adicionales de ' . $service->name : 'Gestión de Servicios Adicionales')
@section('page-title', isset($service) ? 'Servicios Adicionales de ' . $service->name : 'Gestión de Servicios Adicionales')

@section('actions')
    <div class="btn-group" role="group">
        @if(isset($service))
            <a href="{{ route('services.services.show', $service->id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al servicio
            </a>
        @else
            <a href="{{ route('services.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al dashboard
            </a>
        @endif

        @if(auth()->user()->hasPermission('create', 'services'))
            <a href="{{ route('services.additional-services.create', isset($service) ? ['service_id' => $service->id] : []) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Servicio Adicional
            </a>
        @endif
    </div>
@endsection

@section('module-content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Servicios Adicionales</h6>
        </div>
        <div class="card-body">
            @if(count($additionalServices) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Servicio</th>
                                <th>Precio</th>
                                <th>Configurable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($additionalServices as $additionalService)
                                <tr>
                                    <td>
                                        <a href="{{ route('services.additional-services.show', $additionalService->id) }}">
                                            {{ $additionalService->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('services.services.show', $additionalService->service_id) }}">
                                            {{ $additionalService->service->name }}
                                        </a>
                                    </td>
                                    <td>S/ {{ number_format($additionalService->price, 2) }}</td>
                                    <td class="text-center">
                                        @if($additionalService->configurable)
                                            <span class="badge badge-info">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btn-group">
                                            <a href="{{ route('services.additional-services.show', $additionalService->id) }}" class="action-btn" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(auth()->user()->hasPermission('edit', 'services'))
                                                <a href="{{ route('services.additional-services.edit', $additionalService->id) }}" class="action-btn" title="Editar servicio adicional">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if(auth()->user()->hasPermission('delete', 'services'))
                                                <form action="{{ route('services.additional-services.destroy', $additionalService->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn" title="Eliminar servicio adicional" onclick="return confirm('¿Está seguro de eliminar este servicio adicional? Esta acción no se puede deshacer.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center">
                    No hay servicios adicionales registrados.
                    @if(auth()->user()->hasPermission('create', 'services'))
                        <a href="{{ route('services.additional-services.create', isset($service) ? ['service_id' => $service->id] : []) }}" class="alert-link">Crear un nuevo servicio adicional</a>.
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
