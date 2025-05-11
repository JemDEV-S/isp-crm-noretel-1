@extends('services::layouts.master')

@section('title', 'Dashboard de Servicios')
@section('page-title', 'Dashboard de Servicios')

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
    <!-- Overview Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Servicios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $servicesCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Planes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $plansCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Servicios Adicionales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $additionalServicesCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Promociones Activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activePromotionsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Services -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Servicios Recientes</h6>
                    <a href="{{ route('services.services.index') }}" class="btn btn-sm btn-primary">
                        Ver todos
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentServices) && count($recentServices) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Tecnología</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentServices as $service)
                                        <tr>
                                            <td>
                                                <a href="{{ route('services.services.show', $service->id) }}">
                                                    {{ $service->name }}
                                                </a>
                                            </td>
                                            <td>{{ ucfirst($service->service_type) }}</td>
                                            <td>{{ ucfirst($service->technology) }}</td>
                                            <td>
                                                @if($service->active)
                                                    <span class="badge badge-success">Activo</span>
                                                @else
                                                    <span class="badge badge-danger">Inactivo</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-3">No hay servicios registrados.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Plans -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">Planes Recientes</h6>
                    <a href="{{ route('services.plans.index') }}" class="btn btn-sm btn-success">
                        Ver todos
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentPlans) && count($recentPlans) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Servicio</th>
                                        <th>Precio</th>
                                        <th>Velocidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPlans as $plan)
                                        <tr>
                                            <td>
                                                <a href="{{ route('services.plans.show', $plan->id) }}">
                                                    {{ $plan->name }}
                                                </a>
                                            </td>
                                            <td>{{ $plan->service->name }}</td>
                                            <td>S/ {{ number_format($plan->price, 2) }}</td>
                                            <td>{{ $plan->download_speed }}/{{ $plan->upload_speed }} Mbps</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-3">No hay planes registrados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Promotions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-danger">Promociones Activas</h6>
            <a href="{{ route('services.promotions.index') }}" class="btn btn-sm btn-danger">
                Ver todas
            </a>
        </div>
        <div class="card-body">
            @if(isset($activePromotions) && count($activePromotions) > 0)
                <div class="row">
                    @foreach($activePromotions as $promotion)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="m-0 font-weight-bold">{{ $promotion->name }}</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">{{ $promotion->description }}</p>
                                    <div class="font-weight-bold mb-2">
                                        @if($promotion->discount_type == 'percentage')
                                            <span class="badge badge-warning p-2">{{ $promotion->discount }}% de descuento</span>
                                        @else
                                            <span class="badge badge-warning p-2">S/ {{ number_format($promotion->discount, 2) }} de descuento</span>
                                        @endif
                                    </div>
                                    <p class="small text-muted">
                                        Válido: {{ $promotion->start_date->format('d/m/Y') }} - {{ $promotion->end_date->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="card-footer bg-light">
                                    <small class="text-muted">Planes aplicables: {{ $promotion->plans->count() }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center py-3">No hay promociones activas.</p>
            @endif
        </div>
    </div>
@endsection
