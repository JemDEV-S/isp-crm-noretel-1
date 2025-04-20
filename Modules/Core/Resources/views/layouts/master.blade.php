<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ISP-CRM</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Estilos personalizados -->
    <link href="{{ asset('modules/core/css/style.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar styles */
        #sidebar {
            min-height: 100vh;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1;
            transition: all 0.2s;
        }

        #sidebar .nav-item {
            margin-bottom: 0.25rem;
        }

        #sidebar .nav-link {
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.35rem;
            margin: 0 0.5rem;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        #sidebar .sidebar-heading {
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: 500;
            color: white;
        }

        #sidebar .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin: 1rem 0;
        }

        #sidebar .nav-item .nav-link i {
            width: 1.25rem;
            margin-right: 0.5rem;
            text-align: center;
        }

        #sidebar .sidebar-footer {
            padding: 1rem;
            font-size: 0.8rem;
        }

        /* Main content area */
        main {
            padding-top: 1.5rem;
            flex: 1;
        }

        /* Navbar styles */
        .topbar {
            height: 4.375rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            background-color: white;
            z-index: 1000;
        }

        /* Card styles */
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border: none;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
        }

        /* Notifications counter */
        .notification-counter {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: #e74a3b;
            color: white;
            font-size: 0.7rem;
            text-align: center;
            line-height: 18px;
        }

        /* Dropdown menu */
        .dropdown-menu {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.35rem;
        }

        /* Sidebar collapse for mobile */
        @media (max-width: 767.98px) {
            #sidebar {
                margin-left: -100%;
                position: fixed;
                min-height: 100vh;
                z-index: 999;
            }

            #sidebar.active {
                margin-left: 0;
            }

            .content-wrapper {
                width: 100%;
                margin-left: 0;
            }

            #sidebarCollapseBtn {
                display: block;
            }
        }

        /* Module groups */
        .sidebar-module-group {
            margin-bottom: 0.75rem;
        }

        .sidebar-module-title {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            margin: 0.5rem 1rem;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="d-flex">
        @auth
            <!-- Sidebar izquierdo cuando está autenticado -->
            <nav id="sidebar" class="bg-dark">
                <div class="sidebar-heading d-flex justify-content-between align-items-center">
                    <span>ISP-CRM</span>
                    <button type="button" id="sidebarCollapseBtn" class="btn btn-sm btn-dark d-md-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="sidebar-divider"></div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('core.dashboard') ? 'active' : '' }}"
                           href="{{ route('core.dashboard') }}">
                           <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                </ul>

                <div class="sidebar-divider"></div>

                <!-- Módulo Sistema -->
                <div class="sidebar-module-group">
                    <div class="sidebar-module-title">
                        <i class="fas fa-cogs"></i> Sistema
                    </div>
                    <ul class="nav flex-column">
                        @if(auth()->user()->canViewModule('users'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.users.*') ? 'active' : '' }}"
                               href="{{ route('core.users.index') }}">
                               <i class="fas fa-users"></i> Usuarios
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->canViewModule('roles'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.roles.*') ? 'active' : '' }}"
                               href="{{ route('core.roles.index') }}">
                               <i class="fas fa-user-shield"></i> Roles y Permisos
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->canViewModule('configuration'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.config.*') ? 'active' : '' }}"
                               href="{{ route('core.config.index') }}">
                               <i class="fas fa-cog"></i> Configuraciones
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->canViewModule('security'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.security.*') ? 'active' : '' }}"
                               href="{{ route('core.security.index') }}">
                               <i class="fas fa-shield-alt"></i> Seguridad
                            </a>
                        </li>
                        @endif

                        @if(auth()->user()->canViewModule('audit'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.audit.*') ? 'active' : '' }}"
                               href="{{ route('core.audit.index') }}">
                               <i class="fas fa-clipboard-list"></i> Auditoría
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Módulo Workflow -->
                <div class="sidebar-module-group">
                    <div class="sidebar-module-title">
                        <i class="fas fa-project-diagram"></i> Procesos
                    </div>
                    <ul class="nav flex-column">
                        @if(auth()->user()->canViewModule('workflows'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.workflows.*') ? 'active' : '' }}"
                               href="{{ route('core.workflows.index') }}">
                               <i class="fas fa-sitemap"></i> Workflows
                            </a>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('core.notifications.*') ? 'active' : '' }}"
                               href="{{ route('core.notifications.index') }}">
                               <i class="fas fa-bell"></i> Notificaciones
                               @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                <span class="notification-counter">{{ $unreadNotificationsCount }}</span>
                               @endif
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar-divider"></div>

                <div class="sidebar-footer text-center text-white-50">
                    <p>© {{ date('Y') }} ISP-CRM v1.0</p>
                </div>
            </nav>
        @endauth

        <!-- Contenido principal -->
        <div class="content-wrapper w-100">
            @auth
            <!-- Navbar superior -->
            <nav class="navbar navbar-expand topbar mb-4 static-top">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-dark d-md-none">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="d-flex align-items-center">
                        <h1 class="h3 text-gray-800 mb-0">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <!-- Dropdown de notificaciones -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                <span class="badge rounded-pill bg-danger">{{ $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown" style="min-width: 280px;">
                                <h6 class="dropdown-header">
                                    Centro de Notificaciones
                                </h6>
                                @if(isset($recentNotifications) && count($recentNotifications) > 0)
                                    @foreach($recentNotifications as $notification)
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('core.notifications.index') }}">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-primary">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">{{ $notification->created_at->format('d/m/Y H:i') }}</div>
                                            <span class="{{ !isset($notification->metadata['read']) || !$notification->metadata['read'] ? 'fw-bold' : '' }}">{{ Str::limit($notification->action_detail, 50) }}</span>
                                        </div>
                                    </a>
                                    @endforeach
                                @else
                                    <a class="dropdown-item text-center small text-gray-500" href="#">No hay notificaciones</a>
                                @endif
                                <a class="dropdown-item text-center small text-gray-500" href="{{ route('core.notifications.index') }}">Ver todas las notificaciones</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Dropdown de usuario -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-none d-lg-inline text-gray-600 small me-2">{{ auth()->user()->username }}</span>
                                <img class="img-profile rounded-circle" width="32" height="32"
                                    src="https://ui-avatars.com/api/?name={{ auth()->user()->username }}&background=random">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('core.change-password') }}">
                                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cambiar contraseña
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('core.auth.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            @endauth

            <!-- Contenedor principal -->
            <div class="container-fluid px-4">
                <!-- Mensajes de alerta -->
                <div class="alerts-container mb-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show">
                            <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i> Hay errores en el formulario:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <!-- Contenido de la página -->
                <div class="content-container mb-4">
                    @yield('content')
                </div>

                <!-- Footer -->
                <footer class="sticky-footer bg-white py-3 mt-auto">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>© {{ date('Y') }} ISP-CRM - Todos los derechos reservados</span>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            const sidebar = document.getElementById('sidebar');

            if (sidebarCollapse) {
                sidebarCollapse.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.toggle('active');
                    }
                });
            }

            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.toggle('active');
                    }
                });
            }

            // Cerrar alertas automáticamente después de 5 segundos
            const alerts = document.querySelectorAll('.alert:not(.alert-danger)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
