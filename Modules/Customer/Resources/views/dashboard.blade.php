@extends('core::layouts.master')

@section('title', 'Dashboard de Clientes')
@section('page-title', 'Dashboard de Clientes')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <p class="lead">Resumen de la gestión de clientes, leads, documentos e interacciones.</p>
    </div>
</div>

<div class="row">
    <!-- Estadísticas de Clientes -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Clientes</h6>
                        <h3 class="mb-0">{{ $totalCustomers }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-primary-subtle text-primary">
                        <i class="fas fa-users fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes Activos -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Clientes Activos</h6>
                        <h3 class="mb-0">{{ $activeCustomers }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-success-subtle text-success">
                        <i class="fas fa-user-check fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes Inactivos -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Clientes Inactivos</h6>
                        <h3 class="mb-0">{{ $inactiveCustomers }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-warning-subtle text-warning">
                        <i class="fas fa-user-times fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes Nuevos -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Nuevos (este mes)</h6>
                        <h3 class="mb-0">{{ $newCustomersThisMonth }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-info-subtle text-info">
                        <i class="fas fa-user-plus fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Leads Totales -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total de Leads</h6>
                        <h3 class="mb-0">{{ $totalLeads }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-primary-subtle text-primary">
                        <i class="fas fa-user-tag fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads No Convertidos -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Leads no Convertidos</h6>
                        <h3 class="mb-0">{{ $unconvertedLeads }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-danger-subtle text-danger">
                        <i class="fas fa-user-clock fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuevos Leads -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Nuevos Leads (este mes)</h6>
                        <h3 class="mb-0">{{ $newLeadsThisMonth }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-info-subtle text-info">
                        <i class="fas fa-chart-line fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Documentos Pendientes -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Documentos Pendientes</h6>
                        <h3 class="mb-0">{{ $pendingDocuments }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-warning-subtle text-warning">
                        <i class="fas fa-file-alt fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interacciones por Seguir -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Interacciones por Seguir</h6>
                        <h3 class="mb-0">{{ $followUpInteractions }}</h3>
                    </div>
                    <div class="icon-shape rounded-circle bg-info-subtle text-info">
                        <i class="fas fa-comments fa-fw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Clientes por Tipo -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Clientes por Tipo
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px; width:100%">
                    <canvas id="customerTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Clientes por Segmento -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Clientes por Segmento
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px; width:100%">
                    <canvas id="customerSegmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimas Interacciones -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-comments"></i> Últimas Interacciones
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInteractions as $interaction)
                            <tr>
                                <td>{{ $interaction->customer->full_name }}</td>
                                <td>{{ $interaction->interaction_type }}</td>
                                <td>{{ $interaction->date->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('customer.interactions.show', $interaction->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay interacciones recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('customer.interactions.index') }}" class="btn btn-primary btn-sm">
                        Ver Todas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Leads -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-tag"></i> Últimos Leads
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads as $lead)
                            <tr>
                                <td>{{ $lead->name }}</td>
                                <td>{{ $lead->contact }}</td>
                                <td>
                                    @if($lead->status == 'new')
                                        <span class="badge bg-primary">Nuevo</span>
                                    @elseif($lead->status == 'contacted')
                                        <span class="badge bg-info">Contactado</span>
                                    @elseif($lead->status == 'qualified')
                                        <span class="badge bg-success">Calificado</span>
                                    @elseif($lead->status == 'converted')
                                        <span class="badge bg-warning">Convertido</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $lead->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer.leads.show', $lead->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay leads recientes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('customer.leads.index') }}" class="btn btn-primary btn-sm">
                        Ver Todos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Datos para el gráfico de tipos de cliente
    const typeData = {
        labels: [
            @foreach($customersByType as $type)
                '{{ ucfirst($type->customer_type) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($customersByType as $type)
                    {{ $type->total }},
                @endforeach
            ],
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    };

    // Datos para el gráfico de segmentos
    const segmentData = {
        labels: [
            @foreach($customersBySegment as $segment)
                '{{ ucfirst($segment->segment ?? "Sin segmento") }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($customersBySegment as $segment)
                    {{ $segment->total }},
                @endforeach
            ],
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    };

    // Configuración de los gráficos
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de tipos
        const typeChart = document.getElementById("customerTypeChart");
        new Chart(typeChart, {
            type: 'doughnut',
            data: typeData,
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de segmentos
        const segmentChart = document.getElementById("customerSegmentChart");
        new Chart(segmentChart, {
            type: 'doughnut',
            data: segmentData,
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush

<style>
.icon-shape {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    vertical-align: middle;
    width: 50px;
    height: 50px;
}

.bg-primary-subtle {
    background-color: rgba(66, 153, 225, 0.15);
}

.bg-success-subtle {
    background-color: rgba(56, 161, 105, 0.15);
}

.bg-warning-subtle {
    background-color: rgba(214, 158, 46, 0.15);
}

.bg-danger-subtle {
    background-color: rgba(229, 62, 62, 0.15);
}

.bg-info-subtle {
    background-color: rgba(49, 130, 206, 0.15);
}
</style>