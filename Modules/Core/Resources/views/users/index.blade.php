@extends('core::layouts.master')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Usuarios</h5>
        @if(auth()->user()->canCreateInModule('users'))
        <a href="{{ route('core.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Nuevo Usuario
        </a>
        @endif
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form action="{{ route('core.users.index') }}" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar por nombre o email" name="search" value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendido</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('core.users.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fa fa-refresh"></i> Reiniciar
                    </a>
                </div>
            </div>
        </form>

        <!-- Tabla de usuarios -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Roles</th>
                        <th>Último acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->status == 'active')
                                <span class="badge bg-success">Activo</span>
                            @elseif($user->status == 'inactive')
                                <span class="badge bg-secondary">Inactivo</span>
                            @elseif($user->status == 'suspended')
                                <span class="badge bg-danger">Suspendido</span>
                            @endif
                        </td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-info">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $user->last_access ? $user->last_access->format('d/m/Y H:i') : 'Nunca' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('core.users.show', $user->id) }}" class="btn btn-info" title="Ver">
                                    <i class="fa fa-eye"></i>
                                </a>

                                @if(auth()->user()->canEditInModule('users'))
                                <a href="{{ route('core.users.edit', $user->id) }}" class="btn btn-primary" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>

                                @if($user->status == 'active')
                                    <button type="button" class="btn btn-warning" title="Desactivar" data-bs-toggle="modal" data-bs-target="#deactivateModal{{ $user->id }}">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success" title="Activar" data-bs-toggle="modal" data-bs-target="#activateModal{{ $user->id }}">
                                        <i class="fa fa-check"></i>
                                    </button>
                                @endif
                                @endif

                                @if(auth()->user()->canDeleteInModule('users'))
                                <button type="button" class="btn btn-danger" title="Eliminar" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>

                            <!-- Modal para activar usuario -->
                            @if(auth()->user()->canEditInModule('users'))
                            <div class="modal fade" id="activateModal{{ $user->id }}" tabindex="-1" aria-labelledby="activateModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="activateModalLabel{{ $user->id }}">Activar Usuario</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que deseas activar al usuario <strong>{{ $user->username }}</strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('core.users.activate', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success">Activar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal para desactivar usuario -->
                            <div class="modal fade" id="deactivateModal{{ $user->id }}" tabindex="-1" aria-labelledby="deactivateModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deactivateModalLabel{{ $user->id }}">Desactivar Usuario</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que deseas desactivar al usuario <strong>{{ $user->username }}</strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('core.users.deactivate', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-warning">Desactivar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Modal para eliminar usuario -->
                            @if(auth()->user()->canDeleteInModule('users'))
                            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Eliminar Usuario</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar al usuario <strong>{{ $user->username }}</strong>?</p>
                                            <p class="text-danger"><strong>Atención:</strong> Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('core.users.destroy', $user->id) }}" method="POST">
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
                        <td colspan="7" class="text-center">No se encontraron usuarios</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
