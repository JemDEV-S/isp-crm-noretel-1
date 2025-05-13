@extends('core::layouts.master')

@section('title', 'Dashboard de Contratos')
@section('page-title', 'Dashboard de Contratos')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .card-counter {
        padding: 20px 10px;
        background-color: #fff;
        height: 100px;
        border-radius: 10px;
        transition: .3s linear all;
        box-shadow: 0 5px 20px rgba(0,0,0,.05);
    }
    .card-counter .icon-container {
        font-size: 4em;
        opacity: 0.4;
    }
    .card-counter .count-numbers {
        position: absolute;
        right: 30px;
        top: 15px;
        font-size: 32px;
        font-weight: 700;
    }
    .card-counter .count-name {
        position: absolute;
        right: 30px;
        top: 65px;
        font-style: italic;
        text-transform: capitalize;
        opacity: 0.7;
        font-size: 14px;
    }
    .primary {
        background-color: #007bff;
        color: #FFF;
    }
    .success {
        background-color: #28a745;
        color: #FFF;
    }
    .danger {
        background-color: #dc3545;
        color: #FFF;
    }
    .warning {
        background-color: #ffc107;
        color: #000;
    }
    .info {
        background-color: #17a2b8;
        color: #FFF;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card-counter primary stat-card">
            <div class="icon-container">
                <i class="fas fa-file-signature"></i>
            </div>
            <span class="count-numbers">{{ $contractsByStatus['active'] ?? 0 }}</span>
            <span class="count-name">Contratos Activos</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-counter success stat-card">
            <div class="icon-container">
                <i class="fas fa-tools"></i>
            </div>
            <span class="count-numbers">{{ count($nearExpirationContracts) }}</span>
            <span class="count-name">Por Vencer</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-counter warning stat-card">
            <div class="icon-container">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <span class="count-numbers">{{ count($expiredContracts) }}</span>
            <span class="count-name">Vencidos</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-counter info stat-card">
            <div class="icon-container">
                <i class="fas fa-map-marked-alt"></i>
            </div>
            <span class="count-numbers">{{ $contractsByStatus['pending_installation'] ?? 0 }}</span>
            <span class="count-name">Pendientes</span>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Contratos por Mes</h5>
            </div>
            <div class="card-body">
                <canvas id="contractsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Contratos por Estado</h5>
            </div>
            <div class="card-body">
                <canvas id="contractStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Contratos por Vencer</h5>
                <a href="{{ route('contract.contracts.near-expiration') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Plan</th>
                                <th>Vence</th>
                                <th>Días</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nearExpirationContracts->take(5) as $contract)
                                <tr>
                                    <td>
                                        {{ $contract->customer->first_name }} {{ $contract->customer->last_name }}
                                    </td>
                                    <td>{{ $contract->plan->name }}</td>
                                    <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-warning">{{ $contract->remaining_time }} días</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('contract.contracts.show', $contract->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($contract->canBeRenewed())
                                            <a href="{{ route('contract.contracts.renew-form', $contract->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-sync-alt"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay contratos por vencer</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Contratos Recientes</h5>
                <a href="{{ route('contract.contracts.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Plan</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentContracts as $contract)
                                <tr>
                                    <td>
                                        {{ $contract->customer->first_name }} {{ $contract->customer->last_name }}
                                    </td>
                                    <td>{{ $contract->plan->name }}</td>
                                    <td>
                                        @if($contract->status == 'active')
                                            <span class="badge bg-success">Activo</span>
                                        @elseif($contract->status == 'pending_installation')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @elseif($contract->status == 'expired')
                                            <span class="badge bg-danger">Vencido</span>
                                        @elseif($contract->status == 'cancelled')
                                            <span class="badge bg-secondary">Cancelado</span>
                                        @elseif($contract->status == 'renewed')
                                            <span class="badge bg-info">Renovado</span>
                                        @else
                                            <span class="badge bg-dark">{{ $contract->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $contract->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('contract.contracts.show', $contract->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay contratos recientes</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico de contratos por mes
    const monthsData = @json($monthlyContractsData);
    const months = Object.keys(monthsData);
    const contractCounts = Object.values(monthsData);
    
    const ctx1 = document.getElementById('contractsChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Contratos por Mes',
                data: contractCounts,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Datos para el gráfico de contratos por estado
    const statusData = @json($contractsByStatus);
    const statuses = Object.keys(statusData).map(key => {
        switch(key) {
            case 'active': return 'Activos';
            case 'pending_installation': return 'Pendientes';
            case 'expired': return 'Vencidos';
            case 'cancelled': return 'Cancelados';
            case 'renewed': return 'Renovados';
            case 'suspended': return 'Suspendidos';
            default: return key;
        }
    });
    const statusCounts = Object.values(statusData);
    
    const ctx2 = document.getElementById('contractStatusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: statuses,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',  // success - active
                    'rgba(255, 193, 7, 0.7)',  // warning - pending
                    'rgba(220, 53, 69, 0.7)',  // danger - expired
                    'rgba(108, 117, 125, 0.7)', // secondary - cancelled
                    'rgba(23, 162, 184, 0.7)',  // info - renewed
                    'rgba(0, 123, 255, 0.7)'    // primary - suspended
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(0, 123, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
});
</script>
@endpush