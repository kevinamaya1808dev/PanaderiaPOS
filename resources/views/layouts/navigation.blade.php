<div class="sidebar-user-info">
    <div class="fw-bold text-dark fs-5">{{ Auth::user()->name ?? 'Usuario' }}</div>
    <small>{{ Auth::user()->cargo->nombre ?? 'N/A' }}</small>
</div>

<ul class="nav flex-column">
    <!-- DASHBOARD -->
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
    </li>
    
    <!-- SECCIÓN ADMINISTRACIÓN -->
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar') || Auth::user()->hasPermissionTo('usuarios', 'mostrar'))
        <li class="nav-item mt-3">
            <div class="sidebar-heading fw-bold ms-3">ADMINISTRACIÓN</div>
        </li>
    @endif
    
    {{-- GESTIÓN DE CARGOS --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('cargos.index') ? 'active ' : '' }}" href="{{ route('cargos.index') }}">
                <i class="fas fa-id-badge me-2"></i> Cargos y Permisos
            </a>
        </li>
    @endif
    
    {{-- GESTIÓN DE EMPLEADOS (Módulo: usuarios) --}}
    @if (Auth::user()->hasPermissionTo('usuarios', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('empleados.index') ? 'active ' : '' }}" href="{{ route('empleados.index') }}">
                <i class="fas fa-users me-2"></i> Gestión de Empleados
            </a>
        </li>
    @endif
    
    <!-- SECCIÓN CATÁLOGO -->
    @if (Auth::user()->hasPermissionTo('productos', 'mostrar') || Auth::user()->hasPermissionTo('inventario', 'mostrar'))
        <li class="nav-item mt-3">
            <div class="sidebar-heading fw-bold ms-3">CATÁLOGO</div>
        </li>
    @endif

{{-- CATEGORÍAS (Módulo: productos) --}}
    @if (Auth::user()->hasPermissionTo('categorias', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('categorias.index') ? 'active ' : '' }}" href="{{ route('categorias.index') }}">
                <i class="fas fa-tags me-2"></i> Categorías
            </a>
        </li>
            @endif
    @if (Auth::user()->hasPermissionTo('productos', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('productos.index') ? 'active ' : '' }}" href="{{ route('productos.index') }}">
                <i class="fas fa-boxes me-2"></i> Productos
            </a>
        </li>
    @endif
    
{{-- INVENTARIO (Módulo: inventario) --}}
@if (Auth::user()->hasPermissionTo('inventario', 'mostrar'))
    <li class="nav-item">
        {{-- 👇 LÍNEA CORREGIDA 👇 --}}
        <a class="nav-link text-white {{ request()->is('inventario*') ? 'active' : '' }}" href="/inventario">
            <i class="fas fa-warehouse me-2"></i> Inventario
        </a>
    </li>
@endif

    <!-- SECCIÓN CRM & COMPRAS -->
    @if (Auth::user()->hasPermissionTo('clientes', 'mostrar') || Auth::user()->hasPermissionTo('proveedores', 'mostrar') || Auth::user()->hasPermissionTo('compras', 'mostrar'))
        <li class="nav-item mt-3">
            <div class="sidebar-heading fw-bold ms-3">CRM & COMPRAS</div>
        </li>
    @endif

    {{-- CLIENTES (Módulo: clientes) --}}
    @if (Auth::user()->hasPermissionTo('clientes', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('clientes.index') ? 'active ' : '' }}" href="{{ route('clientes.index') }}">
                <i class="fas fa-address-book me-2"></i> Clientes (CRM)
            </a>
        </li>
    @endif

{{-- PROVEEDORES & COMPRAS --}}
@if (Auth::user()->hasPermissionTo('proveedores', 'mostrar'))
    <li class="nav-item">
        {{-- 👇 SE AÑADIÓ LA CONDICIÓN 'active' AQUÍ 👇 --}}
        <a class="nav-link text-white {{ request()->is('proveedores*') ? 'active' : '' }}" href="/proveedores">
            <i class="fas fa-truck-moving me-2"></i> Proveedores
        </a>
    </li>
@endif

@if (Auth::user()->hasPermissionTo('compras', 'mostrar'))
    <li class="nav-item">
        {{-- 👇 Y TAMBIÉN AQUÍ 👇 --}}
        <a class="nav-link text-white {{ request()->is('compras*') ? 'active' : '' }}" href="/compras">
            <i class="fas fa-shopping-basket me-2"></i> Compras
        </a>
    </li>
@endif

    <!-- SECCIÓN POS & CAJA -->
    @if (Auth::user()->hasPermissionTo('ventas', 'mostrar') || Auth::user()->hasPermissionTo('cajas', 'mostrar'))
        <li class="nav-item mt-3">
            <div class="sidebar-heading fw-bold ms-3">POS & CAJA</div>
        </li>
    @endif

    {{-- VENTAS (Módulo: ventas) --}}
    @if (Auth::user()->hasPermissionTo('ventas', 'mostrar'))
        <li class="nav-item">
            {{-- CORRECCIÓN FINAL: Apuntar a la ruta 'ventas.tpv' (GET /tpv) --}}
            <a class="nav-link text-white {{ request()->routeIs('ventas.tpv') ? 'active' : '' }}" href="{{ route('ventas.tpv') }}">
                <i class="fas fa-cash-register me-2"></i> Ventas (POS)
            </a>
        </li>
    @endif

    {{-- CAJAS (Módulo: cajas) --}}
    @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('cajas.index') ? 'active' : '' }}" href="{{ route('cajas.index') }}">
                <i class="fas fa-dollar-sign me-2"></i> Flujo de Caja
            </a>
        </li>
    @endif

</ul>