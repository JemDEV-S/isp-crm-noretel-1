@extends('core::layouts.master')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Tarjeta de bienvenida -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Bienvenido, {{ auth()->user()->username }}</h5>
                <p class="card-text">Esta es la plataforma de gestión ISP-CRM. Desde aquí puedes administrar todos los aspectos de tu sistema.</p>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Usuarios</h6>
                        <h4 class="mb-0">{{ $stats['users'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-primary text-white p-3 rounded">
                        <i class="fa fa-users fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-success">
                        <i class="fa fa-arrow-up"></i> {{ $stats['new_users'] ?? 0 }} nuevos
                    </span> 
                    este mes
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Roles</h6>
                        <h4 class="mb-0">{{ $stats['roles'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-success text-white p-3 rounded">
                        <i class="fa fa-key fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    Con <span class="text-primary">{{ $stats['permissions'] ?? 0 }}</span> permisos configurados
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Notificaciones</h6>
                        <h4 class="mb-0">{{ $stats['notifications'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-info text-white p-3 rounded">
                        <i class="fa fa-bell fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-danger">{{ $stats['unread_notifications'] ?? 0 }}</span> sin leer
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow-sm border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Workflows</h6>
                        <h4 class="mb-0">{{ $stats['workflows'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-warning text-white p-3 rounded">
                        <i class="fa fa-sitemap fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-primary">{{ $stats['active_workflows'] ?? 0 }}</span> activos
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actividad reciente -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Actividad reciente</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentActivities ?? [] as $activity)
                    <li class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $activity->action_type }}</h6>
                            <small>{{ $activity->action_date->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ $activity->action_detail }}</p>
                        <small>{{ $activity->user->username ?? 'Sistema' }}</small>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">
                        No hay actividad reciente
                    </li>
                    @endforelse
                </ul>
            </div>
            @if(count($recentActivities ?? []) > 0)
            <div class="card-footer text-center">
                <a href="#" class="btn btn-sm btn-link">Ver todas las actividades</a>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Notificaciones recientes -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Notificaciones recientes</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentNotifications ?? [] as $notification)
                    <li class="list-group-item {{ isset($notification->metadata['read']) && $notification->metadata['read'] ? '' : 'list-group-item-light' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $notification->type }}</h6>
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ Str::limit($notification->content, 100) }}</p>
                        <small>{{ $notification->channel }}</small>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">
                        No hay notificaciones recientes
                    </li>
                    @endforelse
                </ul>
            </div>
            @if(count($recentNotifications ?? []) > 0)
            <div class="card-footer text-center">
                <a href="{{ route('core.notifications.index') }}" class="btn btn-sm btn-link">Ver todas las notificaciones</a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Componentes del sistema -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Componentes del sistema</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Componente</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemComponents ?? [] as $component)
                            <tr>
                                <td>{{ $component['name'] }}</td>
                                <td>
                                    @if($component['status'] === 'active')
                                    <span class="badge bg-success">Activo</span>
                                    @elseif($component['status'] === 'warning')
                                    <span class="badge bg-warning">Advertencia</span>
                                    @elseif($component['status'] === 'error')
                                    <span class="badge bg-danger">Error</span>
                                    @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>{{ $component['description'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Código JavaScript adicional para el dashboard
    $(document).ready(function() {
        console.log('Dashboard cargado correctamente');
        
        // Aquí puedes agregar lógica adicional para el dashboard
        // como actualización de datos en tiempo real, gráficos, etc.
    });
</script>
@endpush