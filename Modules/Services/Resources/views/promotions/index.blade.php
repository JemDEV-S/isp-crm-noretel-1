@extends('services::layouts.master')

@section('title', 'Gestión de Promociones')
@section('page-title', 'Gestión de Promociones')

@section('actions')
    <div class="btn-group" role="group">
        <a href="{{ route('services.dashboard') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al dashboard
        </a>

        @if(auth()->user()->hasPermission('create', 'services'))
            <a href="{{ route('services.promotions.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Nueva Promoción
            </a>
        @endif
    </div>
@endsection

@section('module-content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Promociones</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                     aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="{{ route('services.promotions.index', ['active' => 1]) }}">
                        <i class="fas fa-check-circle fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar solo activas
                    </a>
                    <a class="dropdown-item" href="{{ route('services.promotions.index') }}">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar todas
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(count($promotions) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descuento</th>
                                <th>Período de Validez</th>
                                <th>Planes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promotions as $promotion)
                                <tr>
                                    <td>
                                        <a href="{{ route('services.promotions.show', $promotion->id) }}">
                                            {{ $promotion->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($promotion->discount_type === 'percentage')
                                            {{ $promotion->discount }}%
                                        @else
                                            S/ {{ number_format($promotion->discount, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $promotion->start_date->format('d/m/Y') }} - {{ $promotion->end_date->format('d/m/Y') }}
                                        @if($promotion->start_date > now())
                                            <span class="badge badge-info">Próxima</span>
                                        @elseif($promotion->end_date < now())
                                            <span class="badge badge-secondary">Finalizada</span>
                                        @else
                                            <span class="badge badge-success">En curso</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $promotion->plans->count() }}
                                    </td>
                                    <td class="text-center">
                                        @if($promotion->active)
                                            <span class="badge badge-success">Activa</span>
                                        @else
                                            <span class="badge badge-danger">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btn-group">
                                            <a href="{{ route('services.promotions.show', $promotion->id) }}" class="action-btn" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(auth()->user()->hasPermission('edit', 'services'))
                                                <a href="{{ route('services.promotions.edit', $promotion->id) }}" class="action-btn" title="Editar promoción">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if($promotion->active)
                                                    <form action="{{ route('services.promotions.deactivate', $promotion->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Desactivar promoción" onclick="return confirm('¿Está seguro de desactivar esta promoción?')">
                                                            <i class="fas fa-toggle-off"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('services.promotions.activate', $promotion->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Activar promoción" onclick="return confirm('¿Está seguro de activar esta promoción?')">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if(auth()->user()->hasPermission('delete', 'services'))
                                                <form action="{{ route('services.promotions.destroy', $promotion->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn" title="Eliminar promoción" onclick="return confirm('¿Está seguro de eliminar esta promoción? Esta acción no se puede deshacer.')">
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
                    No hay promociones registradas.
                    @if(auth()->user()->hasPermission('create', 'services'))
                        <a href="{{ route('services.promotions.create') }}" class="alert-link">Crear una nueva promoción</a>.
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
