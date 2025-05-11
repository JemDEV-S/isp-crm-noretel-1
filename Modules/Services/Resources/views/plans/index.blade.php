@extends('services::layouts.master')

@section('title', isset($service) ? 'Planes de ' . $service->name : 'Gestión de Planes')
@section('page-title', isset($service) ? 'Planes de ' . $service->name : 'Gestión de Planes')

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
            <a href="{{ route('services.plans.create', isset($service) ? ['service_id' => $service->id] : []) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Plan
            </a>
        @endif
    </div>
@endsection

@section('module-content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Planes</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                     aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Opciones:</div>
                    <a class="dropdown-item" href="{{ route('services.plans.index', isset($service) ? ['service_id' => $service->id, 'active' => 1] : ['active' => 1]) }}">
                        <i class="fas fa-check-circle fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar solo activos
                    </a>
                    <a class="dropdown-item" href="{{ route('services.plans.index', isset($service) ? ['service_id' => $service->id, 'active' => 0] : ['active' => 0]) }}">
                        <i class="fas fa-times-circle fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar solo inactivos
                    </a>
                    <a class="dropdown-item" href="{{ route('services.plans.index', isset($service) ? ['service_id' => $service->id] : []) }}">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Mostrar todos
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(count($plans) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Servicio</th>
                                <th>Velocidad</th>
                                <th>Precio</th>
                                <th>Promoción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                <tr>
                                    <td>
                                        <a href="{{ route('services.plans.show', $plan->id) }}">
                                            {{ $plan->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('services.services.show', $plan->service_id) }}">
                                            {{ $plan->service->name }}
                                        </a>
                                    </td>
                                    <td>{{ $plan->download_speed }}/{{ $plan->upload_speed }} Mbps</td>
                                    <td>
                                        @if($plan->getDiscountedPrice() < $plan->price)
                                            <span class="text-decoration-line-through text-muted">S/ {{ number_format($plan->price, 2) }}</span>
                                            <span class="text-success fw-bold">S/ {{ number_format($plan->getDiscountedPrice(), 2) }}</span>
                                        @else
                                            S/ {{ number_format($plan->price, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $activePromotion = $plan->promotions()
                                                ->where('active', true)
                                                ->where('start_date', '<=', now())
                                                ->where('end_date', '>=', now())
                                                ->first();
                                        @endphp

                                        @if($activePromotion)
                                            <span class="badge badge-warning">
                                                {{ $activePromotion->name }}
                                                @if($activePromotion->discount_type === 'percentage')
                                                    ({{ $activePromotion->discount }}%)
                                                @else
                                                    (S/ {{ number_format($activePromotion->discount, 2) }})
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">Sin promoción</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($plan->active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btn-group">
                                            <a href="{{ route('services.plans.show', $plan->id) }}" class="action-btn" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(auth()->user()->hasPermission('edit', 'services'))
                                                <a href="{{ route('services.plans.edit', $plan->id) }}" class="action-btn" title="Editar plan">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @if($plan->active)
                                                    <form action="{{ route('services.plans.deactivate', $plan->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Desactivar plan" onclick="return confirm('¿Está seguro de desactivar este plan?')">
                                                            <i class="fas fa-toggle-off"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('services.plans.activate', $plan->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="action-btn" title="Activar plan" onclick="return confirm('¿Está seguro de activar este plan?')">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if(auth()->user()->hasPermission('delete', 'services'))
                                                <form action="{{ route('services.plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn" title="Eliminar plan" onclick="return confirm('¿Está seguro de eliminar este plan? Esta acción no se puede deshacer.')">
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
                    No hay planes registrados.
                    @if(auth()->user()->hasPermission('create', 'services'))
                        <a href="{{ route('services.plans.create', isset($service) ? ['service_id' => $service->id] : []) }}" class="alert-link">Crear un nuevo plan</a>.
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
