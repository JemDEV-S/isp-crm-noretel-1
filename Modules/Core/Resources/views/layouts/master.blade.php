<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ISP-CRM') - Sistema ISP-CRM</title>

    <!-- Estilos CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <link href="{{ asset('modules/core/css/style.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            @auth
                <!-- Sidebar izquierdo cuando está autenticado -->
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="text-center mb-4">
                            <h5 class="text-white">ISP-CRM</h5>
                            <p class="text-white-50">Sistema de Gestión</p>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.dashboard') ? 'active' : '' }}"
                                   href="{{ route('core.dashboard') }}">
                                   <i class="fa fa-dashboard"></i> Dashboard
                                </a>
                            </li>

                            @if(auth()->user()->canViewModule('users'))
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.users.*') ? 'active' : '' }}"
                                   href="{{ route('core.users.index') }}">
                                   <i class="fa fa-users"></i> Usuarios
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->canViewModule('roles'))
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.roles.*') ? 'active' : '' }}"
                                   href="{{ route('core.roles.index') }}">
                                   <i class="fa fa-key"></i> Roles y Permisos
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->canViewModule('configuration'))
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.config.*') ? 'active' : '' }}"
                                   href="{{ route('core.config.index') }}">
                                   <i class="fa fa-cogs"></i> Configuraciones
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->canViewModule('workflows'))
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.workflows.*') ? 'active' : '' }}"
                                   href="{{ route('core.workflows.index') }}">
                                   <i class="fa fa-sitemap"></i> Workflows
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->canViewModule('security'))
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.security.*') ? 'active' : '' }}"
                                   href="{{ route('core.security.index') }}">
                                   <i class="fa fa-shield"></i> Seguridad
                                </a>
                            </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('core.notifications.*') ? 'active' : '' }}"
                                   href="{{ route('core.notifications.index') }}">
                                   <i class="fa fa-bell"></i> Notificaciones
                                </a>
                            </li>
                        </ul>

                        <hr class="text-white-50">

                        <div class="text-center text-white-50 small">
                            <p>© {{ date('Y') }} ISP-CRM</p>
                        </div>
                    </div>
                </nav>
            @endauth

            <!-- Contenido principal -->
            <main class="{{ auth()->check() ? 'col-md-9 col-lg-10 ms-sm-auto' : 'col-12' }} px-md-4">
                @auth
                <!-- Header para usuarios autenticados -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user"></i> {{ auth()->user()->username }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('core.change-password') }}">Cambiar contraseña</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('core.auth.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Cerrar sesión</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- Mensajes de alerta -->
                <div class="alerts-container mb-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <!-- Contenido de la página -->
                <div class="content-container">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <!-- Scripts personalizados -->
    <script src="{{ asset('modules/core/js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
