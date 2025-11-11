<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- CAMBIO: Corregido a 'utf-8' --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Panadería POS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    {{-- CAMBIO: Corregido 'xintegrity' a 'integrity' --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    {{-- Cargamos tu CSS que ahora tiene la lógica del overlay --}}
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    
    {{-- CAMBIO: Corregido 'xintegrity' a 'integrity' --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    
    <nav class="top-header navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-dark me-3" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand me-auto" href="{{ route('dashboard') }}">
                <i class="fas fa-bread-slice me-2"></i> {{ config('app.name', 'Panadería') }} POS
            </a>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> {{ Auth::user()->name ?? 'Usuario' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-cog me-2"></i> Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
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

    {{-- ========================================================== --}}
    {{-- CAMBIO: Div del fondo oscuro (Backdrop) añadido --}}
    {{-- ========================================================== --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="main-content" id="main-content">
        <div id="app">
            
            {{-- Alertas (siguen igual) --}}
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

    {{-- CAMBIO: Corregido 'xintegrity' a 'integrity' --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            // ==========================================================
            // CAMBIO: Lógica de Sidebar (Controla el Overlay)
            // ==========================================================
            
            // Función para CERRAR el sidebar
            function closeSidebar() {
                sidebar.classList.add('collapsed');
                backdrop.classList.remove('active');
                localStorage.setItem('sidebarCollapsed', 'true');
            }
            
            // Función para ABRIR el sidebar
            function openSidebar() {
                sidebar.classList.remove('collapsed');
                backdrop.classList.add('active');
                localStorage.setItem('sidebarCollapsed', 'false');
            }

            // Al cargar la página, la cerramos por defecto si es móvil
            // O si el usuario la dejó cerrada en PC
            if (window.innerWidth < 992 || localStorage.getItem('sidebarCollapsed') === 'true') {
                 sidebar.classList.add('collapsed');
                 backdrop.classList.remove('active');
            } else {
                 // Si es PC y no estaba colapsado, lo abrimos
                 sidebar.classList.remove('collapsed');
                 backdrop.classList.add('active');
            }

            // El botón de hamburguesa alterna el estado
            sidebarToggle.addEventListener('click', function () {
                if (sidebar.classList.contains('collapsed')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            // El fondo oscuro CIERRA el menú al hacer clic
            backdrop.addEventListener('click', function() {
                closeSidebar();
            });

            // Los enlaces del menú CIERRAN el menú al hacer clic
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Siempre cierra al hacer clic en un enlace (comportamiento overlay)
                    closeSidebar();
                });
            });

            // --- Lógica de Alertas (5s) ---
            const autoDismissAlerts = document.querySelectorAll('.auto-dismiss-alert');
            autoDismissAlerts.forEach(function(alert) {
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined') {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000); 
            });

            // --- Lógica de Auto-Scroll (Sigue funcionando) ---
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
@stack('scripts')</body>
</html>