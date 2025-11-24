<div class="sidebar-user-info">
    <div class="fw-bold text-dark fs-5">{{ Auth::user()->name ?? 'Usuario' }}</div>
    <small>{{ Auth::user()->cargo->nombre ?? 'N/A' }}</small>
</div>

<ul class="nav flex-column">
    <li class="nav-item">
        {{-- DASHBOARD --}}
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
    </li>
    
    {{-- =============================================== --}}
    {{-- SECCIÓN: ADMINISTRACIÓN --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar') || Auth::user()->hasPermissionTo('usuarios', 'mostrar') || Auth::user()->hasPermissionTo('clientes', 'mostrar'))
        <hr class="my-2 mx-3" style="border-top: 5px solid var(--color-header); opacity: 0.25;">
        <li class="nav-item">
            <div class="sidebar-heading fw-bold ms-3">ADMINISTRACIÓN</div>
        </li>
    @endif
    
    {{-- GESTIÓN DE CARGOS --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}">
                <i class="fas fa-id-badge me-2"></i> Cargos y Permisos
            </a>
        </li>
    @endif
    
    {{-- GESTIÓN DE EMPLEADOS --}}
    @if (Auth::user()->hasPermissionTo('usuarios', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}">
                <i class="fas fa-users me-2"></i> Gestión de Empleados
            </a>
        </li>
    @endif

    {{-- CLIENTES/EXPENDIOS --}}
    @if (Auth::user()->hasPermissionTo('clientes', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                <i class="fas fa-address-book me-2"></i> Expendios
            </a>
        </li>
    @endif

    {{-- ========================================================= --}}
    {{-- AQUÍ ESTÁ: HISTORIAL DE TURNOS (En Administración)       --}}
    {{-- ========================================================= --}}
    {{-- Usamos 'cargos' como permiso base ya que es una función administrativa, 
         igual que lo tenías en el dashboard --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar')) 
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('historial_cajas.*') ? 'active' : '' }}" href="{{ route('historial_cajas.index') }}">
                <i class="fas fa-history me-2"></i> Historial de Turnos
            </a>
        </li>
    @endif

    
    {{-- =============================================== --}}
    {{-- SECCIÓN: PRODUCTOS --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('categorias', 'mostrar') || Auth::user()->hasPermissionTo('productos', 'mostrar') || Auth::user()->hasPermissionTo('inventario', 'mostrar'))
        <hr class="my-2 mx-3" style="border-top: 5px solid var(--color-header); opacity: 0.25;">
        <li class="nav-item">
            <div class="sidebar-heading fw-bold ms-3">PRODUCTOS</div>
        </li>
    @endif

    {{-- CATEGORÍAS --}}
    @if (Auth::user()->hasPermissionTo('categorias', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">
                <i class="fas fa-tags me-2"></i> Categorías
            </a>
        </li>
    @endif

    {{-- PRODUCTOS --}}
    @if (Auth::user()->hasPermissionTo('productos', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                <i class="fas fa-boxes me-2"></i> Productos
            </a>
        </li>
    @endif
    
    {{-- INVENTARIO --}}
    @if (Auth::user()->hasPermissionTo('inventario', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}" href="{{ route('inventario.index') }}">
                <i class="fas fa-warehouse me-2"></i> Inventario
            </a>
        </li>
    @endif

    {{-- =============================================== --}}
    {{-- SECCIÓN: GASTOS Y PAGOS --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('proveedores', 'mostrar') || Auth::user()->hasPermissionTo('compras', 'mostrar'))
        <hr class="my-2 mx-3" style="border-top: 5px solid var(--color-header); opacity: 0.25;">
        <li class="nav-item">
            <div class="sidebar-heading fw-bold ms-3">GASTOS Y PAGOS</div>
        </li>
    @endif

    {{-- PROVEEDORES --}}
    @if (Auth::user()->hasPermissionTo('proveedores', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}" href="{{ route('proveedores.index') }}">
                <i class="fas fa-truck-moving me-2"></i> Proveedores
            </a>
        </li>
    @endif

    {{-- COMPRAS --}}
    @if (Auth::user()->hasPermissionTo('compras', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('compras.*') ? 'active' : '' }}" href="{{ route('compras.index') }}">
                <i class="fas fa-shopping-basket me-2"></i> Compras
            </a>
        </li>
    @endif

    {{-- =============================================== --}}
    {{-- SECCIÓN: OPERACIONES DE CAJA --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('ventas', 'mostrar') || Auth::user()->hasPermissionTo('cajas', 'mostrar'))
        <hr class="my-2 mx-3" style="border-top: 5px solid var(--color-header); opacity: 0.25;">
        <li class="nav-item"> 
            <div class="sidebar-heading fw-bold ms-3">OPERACIONES DE CAJA</div>
        </li>
    @endif

    {{-- VENTAS (POS) --}}
    @if (Auth::user()->hasPermissionTo('ventas', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ventas.tpv') ? 'active' : '' }}" href="{{ route('ventas.tpv') }}">
                <i class="fas fa-cash-register me-2"></i> Terminal de Venta
            </a>
        </li>
    @endif

    {{-- CAJAS (Flujo actual) --}}
    @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('cajas.*') ? 'active' : '' }}" href="{{ route('cajas.index') }}">
                <i class="fas fa-dollar-sign me-2"></i> Flujo de Caja
            </a>
        </li>
        
        {{-- COBRAR PENDIENTES --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('cobrar.*') ? 'active' : '' }}" href="{{ route('cobrar.index') }}">
                <i class="fas fa-file-invoice-dollar me-2"></i> Cobrar Pendientes
            </a>
        </li>
    @endif
</ul>