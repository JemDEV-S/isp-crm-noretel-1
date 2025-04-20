@extends('core::layouts.master')

@section('title', 'Detalle de Rol')
@section('page-title', 'Detalle de Rol')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Información del Rol</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="d-inline-block bg-primary text-white rounded-circle p-3 mb-3">
                        <i class="fa fa-key fa-2x"></i>
                    </div>
                    <h5>{{ $role->name }}</h5>
                    @if($role->active)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <h6>Descripción:</h6>
                    <p class="text-muted">{{ $role->description ?: 'Sin descripción' }}</p>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Cantidad de permisos</span>
                        <span class="badge bg-primary rounded-pill">{{ $role->permissions->count() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Usuarios con este rol</span>
                        <span class="badge bg-info rounded-pill">{{ $role->users->count() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Fecha de creación</span>
                        <span>{{ $role->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Última actualización</span>
                        <span>{{ $role->updated_at->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('core.roles.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('core.roles.edit', $role->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Permisos del Rol</h5>
            </div>
            <div class="card-body">
                @if(count($permissionsByModule) > 0)
                    <div class="accordion" id="accordionPermissions">
                        @foreach($permissionsByModule as $module => $permissions)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $module }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $module }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $module }}">
                                        <strong>{{ ucfirst($module) }}</strong>
                                        <span class="badge bg-primary ms-2">{{ count($permissions) }}</span>
                                    </button>
                                </h2>
                                <div id="collapse{{ $module }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $module }}" data-bs-parent="#accordionPermissions">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Acción</th>
                                                        <th>Condiciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($permissions as $permission)
                                                        <tr>
                                                            <td>{{ $permission->action }}</td>
                                                            <td>
                                                                @if($permission->conditions)
                                                                    <code>{{ json_encode($permission->conditions) }}</code>
                                                                @else
                                                                    <span class="text-muted">Sin condiciones</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Este rol no tiene permisos asignados.
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Usuarios con este Rol</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignRoleModal">
                    <i class="fa fa-plus"></i> Asignar a Usuario
                </button>
            </div>
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
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
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('core.users.show', $user->id) }}" class="btn btn-info" title="Ver usuario">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger" title="Quitar rol" data-bs-toggle="modal" data-bs-target="#removeRoleModal{{ $user->id }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Modal para quitar rol -->
                                            <div class="modal fade" id="removeRoleModal{{ $user->id }}" tabindex="-1" aria-labelledby="removeRoleModalLabel{{ $user->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="removeRoleModalLabel{{ $user->id }}">Quitar Rol</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de que deseas quitar el rol <strong>{{ $role->name }}</strong> al usuario <strong>{{ $user->username }}</strong>?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <form action="{{ route('core.roles.remove') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                                <button type="submit" class="btn btn-danger">Quitar Rol</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No hay usuarios con este rol.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar rol a usuario -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignRoleModalLabel">Asignar Rol a Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('core.roles.assign') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Seleccionar Usuario</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Seleccionar...</option>
                            @foreach(\Modules\Core\Entities\User::whereDoesntHave('roles', function($query) use ($role) {
                                $query->where('role_id', $role->id);
                            })->get() as $availableUser)
                                <option value="{{ $availableUser->id }}">{{ $availableUser->username }} ({{ $availableUser->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection