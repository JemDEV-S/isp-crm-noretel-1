@extends('core::layouts.master')

@section('title', 'Contratos')
@section('page-title', 'Gestión de Contratos')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Contratos Activos</h5>
                <h2>{{ $contractsByStatus['active'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body text-center">
                <h5 class="card-title">Por Vencer</h5>
                <h2>{{ $nearExpirationCount }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Vencidos</h5>
                <h2>{{ $expiredCount }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Pendientes</h5>
                <h2>{{ $contractsByStatus['pending_installation'] ?? 0 }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Contratos</h5>
        @if(auth()->user()->canCreateInModule('contracts'))
        <a href="{{ route('contract.contracts.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Nuevo Contrato
        </a>
        @endif
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="{{ route('contract.contracts.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar cliente" name="search" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="pending_installation" {{ request('status') == 'pending_installation' ? 'selected' : '' }}>Pendiente de instalación</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Vencido</option>
                        <option value="renewed" {{ request('status') == 'renewed' ? 'selected' : '' }}>Renovado</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendido</option>
                    </select>
                </div>
                <div class="col-md-4">
                    @if(isset($customers))
                    <select name="customer_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los clientes</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->identity_document }})
                            </option>
                        @endforeach
                    </select>
                    @endif
                </div>
                <div class="col-md-2">
                    <a href="{{ route('contract.contracts.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fa fa-refresh"></i> Reiniciar
                    </a>
                </div>
            </div>
        </form>

        <!-- Tabla de contratos -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr>
                        <td>{{ $contract->id }}</td>
                        <td>
                            <a href="{{ route('customer.customers.show', $contract->customer_id) }}">
                                {{ $contract->customer->first_name }} {{ $contract->customer->last_name }}
                            </a>
                        </td>
                        <td>{{ $contract->plan->name }}</td>
                        <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                        <td>{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indefinido' }}</td>
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
                            @elseif($contract->status == 'suspended')
                                <span class="badge bg-dark">Suspendido</span>
                            @else
                                <span class="badge bg-dark">{{ $contract->status }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($contract->final_price, 2) }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('contract.contracts.show', $contract->id) }}" class="btn btn-info" title="Ver">
                                    <i class="fa fa-eye"></i>
                                </a>

                                @if(auth()->user()->canEditInModule('contracts'))
                                <a href="{{ route('contract.contracts.edit', $contract->id) }}" class="btn btn-primary" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>

                                @if($contract->status == 'pending_installation')
                                    <a href="{{ route('contract.installations.create', ['contract_id' => $contract->id]) }}" class="btn btn-warning" title="Programar Instalación">
                                        <i class="fa fa-tools"></i>
                                    </a>
                                @endif

                                @if($contract->canBeRenewed())
                                    <a href="{{ route('contract.contracts.renew-form', $contract->id) }}" class="btn btn-success" title="Renovar">
                                        <i class="fa fa-sync-alt"></i>
                                    </a>
                                @endif

                                @if($contract->status == 'active' || $contract->status == 'renewed')
                                    <button type="button" class="btn btn-secondary" title="Cancelar" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $contract->id }}">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                @endif
                                @endif

                                @if(auth()->user()->canDeleteInModule('contracts'))
                                <button type="button" class="btn btn-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $contract->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>

                            <!-- Modal para cancelar contrato -->
                            @if(auth()->user()->canEditInModule('contracts'))
                            <div class="modal fade" id="cancelModal{{ $contract->id }}" tabindex="-1" aria-labelledby="cancelModalLabel{{ $contract->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="cancelModalLabel{{ $contract->id }}">Cancelar Contrato</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('contract.contracts.cancel', $contract->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas cancelar el contrato de <strong>{{ $contract->customer->first_name }} {{ $contract->customer->last_name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label for="reason" class="form-label">Motivo de la cancelación</label>
                                                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                <button type="submit" class="btn btn-danger">Cancelar Contrato</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Modal para eliminar contrato -->
                            @if(auth()->user()->canDeleteInModule('contracts'))
                            <div class="modal fade" id="deleteModal{{ $contract->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $contract->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $contract->id }}">Eliminar Contrato</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar el contrato de <strong>{{ $contract->customer->first_name }} {{ $contract->customer->last_name }}</strong>?</p>
                                            <p class="text-danger"><strong>Atención:</strong> Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <form action="{{ route('contract.contracts.destroy', $contract->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron contratos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $contracts->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection