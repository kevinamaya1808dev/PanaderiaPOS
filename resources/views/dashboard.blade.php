@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dashboard Principal</h2>
    <p class="lead text-secondary">
        Bienvenido, {{ Auth::user()->name }}. Tu cargo actual es: 
        <span class="badge bg-info text-dark">{{ Auth::user()->cargo->nombre ?? 'N/A' }}</span>.
    </p>

    <h4 class="mt-5 mb-3">Accesos Directos a Módulos</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        
        {{-- Módulo 1: Categorías (Módulo: productos) --}}
        @if (Auth::user()->hasPermissionTo('productos', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-tags me-2"></i> Catálogo de Categorías</h5>
                        <p class="card-text">Define y organiza los grupos de productos (Pan Salado, Pastelería, Bebidas).</p>
                        <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-primary">Ir a Categorías →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo 2: Empleados (Módulo: usuarios) --}}
        @if (Auth::user()->hasPermissionTo('usuarios', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-users me-2"></i> Gestión de Empleados</h5>
                        <p class="card-text">Crea, edita y gestiona las cuentas de acceso de todo el personal.</p>
                        <a href="{{ route('empleados.index') }}" class="btn btn-sm btn-outline-primary">Ir a Empleados →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo 3: Cargos y Permisos (Módulo: cargos) --}}
        @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-id-badge me-2"></i> Cargos y Permisos</h5>
                        <p class="card-text">Administra los roles de usuario y sus permisos de acceso (RBAC).</p>
                        <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-primary">Ir a Cargos →</a>
                    </div>
                </div>
            </div>
        @endif
        
    </div>
</div>
@endsection