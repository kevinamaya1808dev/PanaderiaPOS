<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Panadería Kairos') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#d97706">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

</head>

<body>
    
    <nav class="top-header navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-dark me-3" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand me-auto" href="{{ route('dashboard') }}">
                <i class="fas fa-bread-slice me-2"></i> {{ config('app.name', 'Panadería') }} Kairos
            </a>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> {{ Auth::user()->name ?? 'Usuario' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="sidebar" id="sidebar">
        @include('layouts.navigation')
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-content" id="main-content">
        <div id="app">
            
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show auto-dismiss-alert" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show auto-dismiss-alert" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            function isMobile() {
                return window.innerWidth < 992;
            }

            function closeSidebar() {
                sidebar.classList.add('collapsed');
                backdrop.classList.remove('active');
                document.body.classList.remove('sidebar-open'); // 🚫 desbloquear scroll fondo

                if (isMobile()) {
                    localStorage.setItem('sidebarCollapsedMobile', 'true');
                } else {
                    localStorage.setItem('sidebarCollapsedDesktop', 'true');
                }
            }
            
            function openSidebar() {
                sidebar.classList.remove('collapsed');
                backdrop.classList.add('active');
                document.body.classList.add('sidebar-open'); // 🚫 bloquear scroll fondo

                if (isMobile()) {
                    localStorage.setItem('sidebarCollapsedMobile', 'false');
                } else {
                    localStorage.setItem('sidebarCollapsedDesktop', 'false');
                }
            }

            // Estado inicial según dispositivo
            if (isMobile()) {
                if (localStorage.getItem('sidebarCollapsedMobile') === 'true') {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.add('collapsed');
                }
            } else {
                if (localStorage.getItem('sidebarCollapsedDesktop') === 'true') {
                    sidebar.classList.add('collapsed');
                }
            }

            sidebarToggle.addEventListener('click', function () {
                if (sidebar.classList.contains('collapsed')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            backdrop.addEventListener('click', function() {
                closeSidebar();
            });

            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    closeSidebar();
                });
            });

            // Auto-cierre de alertas
            const autoDismissAlerts = document.querySelectorAll('.auto-dismiss-alert');
            autoDismissAlerts.forEach(function(alert) {
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined') {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });

            // Centrar el link activo
            try {
                const activeLink = document.querySelector('.sidebar .nav-link.active');
                if (sidebar && activeLink) {
                    const linkTop = activeLink.offsetTop;
                    const sidebarHeight = sidebar.clientHeight;
                    const linkHeight = activeLink.clientHeight;
                    sidebar.scrollTop = linkTop - (sidebarHeight / 2) + (linkHeight / 2);
                }
            } catch (e) {
                console.error("Error al auto-scroll del sidebar:", e);
            }

        });
    </script>
@stack('scripts')
</body>
</html>
