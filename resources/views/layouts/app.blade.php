<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Panadería POS') }}</title>

    <!-- FUENTE: Inter de Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- ESTILOS CSS de Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- ICONOS de Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJc5xU25j1QzP0K+b/L/x25tHnN0Pz77I9W0Ym71iXq1rE1f0Q2R2E5V5w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
            padding-top: 56px; /* Espacio para el header superior fijo */
        }
        
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1031;
            height: 56px;
        }

        .sidebar {
            width: 250px;
            height: 100vh; 
            position: fixed;
            top: 56px; 
            left: 0;
            z-index: 1030; 
            background-color: #212529;
            transition: all 0.3s;
            overflow-y: auto; 
            padding-bottom: 20px;
        }
        
        .sidebar.collapsed {
            margin-left: -250px;
        }

        .main-content {
            margin-left: 250px; 
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        .main-content.collapsed {
            margin-left: 0;
        }

        .sidebar-user-info {
            padding: 1rem;
            color: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .nav-link.active {
            font-weight: 600;
        }
    </style>
</head>
<body>
    
    <!-- HEADER SUPERIOR (Botón de Hamburguesa y Usuario) -->
    <nav class="top-header navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <!-- Botón de Hamburguesa (CORREGIDO) -->
            <button class="btn btn-dark me-3" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand me-auto" href="{{ route('dashboard') }}">
                <i class="fas fa-bread-slice me-2"></i> {{ config('app.name', 'Panadería') }} POS
            </a>
            
            <!-- Dropdown de Usuario y Logout -->
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
    
    <!-- BARRA LATERAL (SIDEBAR) -->
    <div class="sidebar" id="sidebar">
        @include('layouts.navigation')
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content" id="main-content">
        <div id="app">
            <!-- Mensajes de Sesión y Errores (Correcto) -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- CONTENIDO ESPECÍFICO DE LA VISTA -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts de JavaScript de Bootstrap 5 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('sidebarToggle').addEventListener('click', function () {
                document.getElementById('sidebar').classList.toggle('collapsed');
                document.getElementById('main-content').classList.toggle('collapsed');
            });
        });
    </script>
</body>
</html>