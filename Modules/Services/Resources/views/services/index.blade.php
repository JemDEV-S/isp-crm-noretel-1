@extends('services::layouts.master')

@section('title', 'Gestión de Servicios')
@section('page-title', 'Gestión de Servicios')

@section('actions')
    <div class="btn-group" role="group">
        @if(auth()->user()->hasPermission('create', 'services'))
            <a href="{{ route('services.services.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Servicio
            </a>
        @endif
    </div>
@endsection

@section('module-content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Servicios</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                     aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="{{ route('services.services.index') }}?filter=active">
                        <i class="fas fa-check-circle fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar solo activos
                    </a>
                    <a class="dropdown-item" href="{{ route('services.services.index') }}?filter=inactive">
                        <i class="fas fa-times-circle fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar solo inactivos
                    </a>
                    <a class="dropdown-item" href="{{ route('services.services.index') }}">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar todos
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($services && count($services) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Tecnología</th>
                                <th>Estado</th>
                                <th>Planes</th>
                                <th>Servicios Adicionales</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($services as $service)
                                <tr>
                                    <td>
                                        <a href="{{ route('services.services.show', $service->id) }}">
                                            {{ $service->name }}
                                        </a>
                                    </td>
                                    <td>{{ ucfirst($service->service_type) }}</td>
                                    <td>{{ ucfirst($service->technology) }}</td>
                                    <td class="text-center">
                                        @if($service->active)
                                             <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $service->plans->count() }}
                                        <a href="{{ route('services.plans.index', ['service_id' => $service->id]) }}" class="btn btn-sm btn-link p-0">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{ $service->additionalServices->count() }}
                                        <a href="{{ route('services.additional-services.index', ['service_id' => $service->id]) }}" class="btn btn-sm btn-link p-0">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-btn-group">
                                            <a href="{{ route('services.services.show', $service->id) }}" class="action-btn" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(auth()->user()->hasPermission('edit', 'services'))
                                                <a href="{{ route('services.services.edit', $service->id) }}" class="action-btn" title="Editar servicio">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if($service->active)
                                                    <form action="{{ route('services.services.deactivate', $service->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Desactivar servicio" onclick="return confirm('¿Está seguro de desactivar este servicio?')">
                                                            <i class="fas fa-toggle-off"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('services.services.activate', $service->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Activar servicio" onclick="return confirm('¿Está seguro de activar este servicio?')">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if(auth()->user()->hasPermission('delete', 'services'))
                                                <form action="{{ route('services.services.destroy', $service->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn" title="Eliminar servicio" onclick="return confirm('¿Está seguro de eliminar este servicio? Esta acción no se puede deshacer.')">
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
                    No hay servicios registrados.
                    @if(auth()->user()->hasPermission('create', 'services'))
                        <a href="{{ route('services.services.create') }}" class="alert-link">Crear un nuevo servicio</a>.
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection


