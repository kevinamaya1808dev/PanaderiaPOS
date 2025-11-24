@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dashboard Principal</h2>
    <p class="lead text-secondary mb-4">
        Bienvenido, {{ Auth::user()->name }}. Tu cargo actual es:
        <span class="badge bg-info text-dark">{{ Auth::user()->cargo->nombre ?? 'N/A' }}</span>.
    </p>

    {{-- =============================================== --}}
    {{-- SECCIÓN 1: ACCESOS DIRECTOS COMPACTOS         --}}
    {{-- =============================================== --}}
    <h4 class="mb-3 text-secondary">Accesos Directos</h4>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-5 g-3 mb-5">
        
        {{-- Módulo 1: Punto de Venta (POS) --}}
        @if (Auth::user()->hasPermissionTo('ventas', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-cash-register fa-2x text-success me-2"></i>
                            <h6 class="card-title text-success fw-bold mb-0">Punto de Venta</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Inicia venta y cobra.</p>
                        <a href="{{ route('ventas.tpv') }}" class="btn btn-sm btn-outline-success w-100">Entrar</a>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Módulo 2: Flujo de Caja --}}
        @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-dollar-sign fa-2x text-success me-2 ps-2"></i>
                            <h6 class="card-title text-success fw-bold mb-0">Flujo de Caja</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Cortes y movimientos.</p>
                        <a href="{{ route('cajas.index') }}" class="btn btn-sm btn-outline-success w-100">Entrar</a>
                    </div>
                </div>
            </div>
            
            {{-- Cobrar Pendientes --}}
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-file-invoice-dollar fa-2x text-warning me-2"></i>
                            <h6 class="card-title text-warning fw-bold mb-0">Cobrar Tickets</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Pagos pendientes.</p>
                        <a href="{{ route('cobrar.index') }}" class="btn btn-sm btn-outline-warning w-100">Entrar</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo: Categorías --}}
        @if (Auth::user()->hasPermissionTo('productos', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-tags fa-2x text-primary me-2"></i>
                            <h6 class="card-title text-primary fw-bold mb-0">Categorías</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Grupos de productos.</p>
                        <a href="{{ route('categorias.index') }}" class="btn btn-sm btn-outline-primary w-100">Entrar</a>
                    </div>
                </div>
            </div>
            
            {{-- Módulo: Productos --}}
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-box fa-2x text-primary me-2"></i>
                            <h6 class="card-title text-primary fw-bold mb-0">Productos</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Gestión de inventario.</p>
                        <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-primary w-100">Entrar</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo: Empleados --}}
        @if (Auth::user()->hasPermissionTo('usuarios', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-users fa-2x text-info me-2"></i>
                            <h6 class="card-title text-info text-dark fw-bold mb-0">Empleados</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Cuentas y acceso.</p>
                        <a href="{{ route('empleados.index') }}" class="btn btn-sm btn-outline-info w-100">Entrar</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo: Cargos --}}
        @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-id-badge fa-2x text-info me-2"></i>
                            <h6 class="card-title text-info text-dark fw-bold mb-0">Cargos</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Roles y permisos.</p>
                        <a href="{{ route('cargos.index') }}" class="btn btn-sm btn-outline-info w-100">Entrar</a>
                    </div>
                </div>
            </div>
            
            {{-- Historial --}}
            <div class="col">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-secondary">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-history fa-2x text-secondary me-2"></i>
                            <h6 class="card-title text-secondary fw-bold mb-0">Historial</h6>
                        </div>
                        <p class="card-text text-muted small lh-sm mb-3">Consulta turnos.</p>
                        <a href="{{ route('historial_cajas.index') }}" class="btn btn-sm btn-outline-secondary w-100">Entrar</a>
                    </div>
                </div>
            </div>
        @endif
    </div>


    {{-- =============================================== --}}
    {{-- SECCIÓN 2: MÉTRICAS Y GRÁFICAS (ABAJO)        --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
        
        <hr class="my-5"> 

        <h3 class="mb-3 text-secondary">Métricas Financieras</h3>

        {{-- FILA 1: SEMANA --}}
        <h5 class="mb-3">Esta Semana <small class="text-muted fs-6">({{ \Carbon\Carbon::now()->startOfWeek()->format('d M') }} - {{ \Carbon\Carbon::now()->endOfWeek()->format('d M') }})</small></h5>
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ingresos Semanales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['ingresos'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-money-bill-wave fa-2x text-gray-300 text-primary opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Costos Semanales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['costos'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-dolly fa-2x text-gray-300 text-warning opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Utilidad Semanal</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['utilidad'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300 text-success opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILA 2: MES --}}
        <h5 class="mb-3">Este Mes <small class="text-muted fs-6">({{ \Carbon\Carbon::now()->format('F Y') }})</small></h5>
        <div class="row mb-5">
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ingresos Mensuales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['ingresos'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300 text-primary opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Costos Mensuales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['costos'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-box-open fa-2x text-gray-300 text-warning opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Utilidad Mensual</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['utilidad'], 2) }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-sack-dollar fa-2x text-gray-300 text-success opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE GRÁFICAS --}}
        <h4 class="mt-5 mb-3">Análisis de Negocio (Este Año)</h4>
        <div class="row">
            {{-- Gráfica Ventas --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        {{-- Título NEGRO --}}
                        <h6 class="m-0 font-weight-bold text-dark">Comparativa: Ventas vs Utilidad Real</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="ventasMesChart" height="120"></canvas>
                        </div>
                        <small class="text-muted mt-2 d-block text-center">Haz clic en una barra para filtrar los productos vendidos en ese mes.</small>
                    </div>
                </div>
            </div>

            {{-- Gráfica Productos --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        {{-- Título NEGRO --}}
                        <h6 class="m-0 font-weight-bold text-dark">Top 5 Productos Vendidos</h6>
                    </div>
                    <div class="card-body">
                        <h5 id="topProductosTitulo" class="text-center mb-3 fw-bold">Todo el Año</h5>
                        <div class="chart-area">
                            <canvas id="topProductosChart"></canvas>
                        </div>
                        
                        {{-- RESUMEN DESTACADO (Llenado por JS, sin variables Blade para evitar error) --}}
                        <div class="row mt-4 text-center border-top pt-3">
                            <div class="col-6 border-end">
                                <p class="text-secondary small mb-1 text-uppercase">Total Piezas</p>
                                <h4 class="fw-bold text-dark mb-0" id="resumenTotalPiezas">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                            </div>
                            <div class="col-6">
                                <p class="text-secondary small mb-1 text-uppercase">Más Vendido</p>
                                <h5 class="fw-bold text-success mb-0 text-truncate px-2" id="resumenProductoEstrella">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h5>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

{{-- JAVASCRIPT --}}
@if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let ventasChart; 
    let topProductosChart;
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const ctxVentas = document.getElementById('ventasMesChart').getContext('2d');
    const ctxProductos = document.getElementById('topProductosChart').getContext('2d');
    const topProductosTitulo = document.getElementById('topProductosTitulo');

    // COLORES DEFINIDOS
    const colorDoradoPan = '#c58d4c'; 
    const colorCafeOscuro = '#4E342E';

    async function cargarDatos(mes = null) {
        let url = '/dashboard-data';
        if (mes) url += `?month=${mes}`;
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            // 1. Dibujar gráficas
            if (!mes) renderVentasChart(data.datos_por_mes);
            renderTopProductosChart(data.top_productos);
            
            // 2. Actualizar título
            topProductosTitulo.innerText = mes ? `Mes: ${meses[mes-1]}` : 'Todo el Año';

            // 3. ACTUALIZAR EL RESUMEN DESTACADO (TOTALES Y ESTRELLA)
            // Calculamos el total de piezas sumando el array
            let totalPiezas = data.top_productos.reduce((acc, item) => acc + parseInt(item.total_cantidad), 0);
            document.getElementById('resumenTotalPiezas').innerText = totalPiezas;

            // Obtenemos el nombre del primero
            let nombreEstrella = data.top_productos.length > 0 ? data.top_productos[0].nombre : 'N/A';
            document.getElementById('resumenProductoEstrella').innerText = nombreEstrella;
            document.getElementById('resumenProductoEstrella').title = nombreEstrella;

        } catch (error) { console.error('Error:', error); }
    }

    function renderVentasChart(datosMes) {
        const ventas = new Array(12).fill(0);
        const utilidad = new Array(12).fill(0);
        
        datosMes.forEach(item => {
            ventas[item.mes - 1] = item.total_ventas;
            utilidad[item.mes - 1] = item.total_utilidad;
        });

        if (ventasChart) ventasChart.destroy();

        ventasChart = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Ventas Totales ($)',
                        data: ventas,
                        backgroundColor: colorDoradoPan,
                        borderColor: colorDoradoPan,
                        borderWidth: 1
                    },
                    {
                        label: 'Utilidad Neta ($)',
                        data: utilidad,
                        backgroundColor: colorCafeOscuro,
                        borderColor: colorCafeOscuro,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: { y: { beginAtZero: true } },
                onClick: (e) => {
                    const elements = ventasChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (elements.length > 0) {
                        cargarDatos(elements[0].index + 1);
                    }
                }
            }
        });
    }

    function renderTopProductosChart(dataProductos) {
        const labels = dataProductos.map(p => p.nombre);
        const valores = dataProductos.map(p => p.total_cantidad);

        if (topProductosChart) topProductosChart.destroy();

        topProductosChart = new Chart(ctxProductos, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Piezas Vendidas',
                    data: valores,
                    backgroundColor: colorDoradoPan,
                    borderColor: colorDoradoPan,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => { cargarDatos(); });
</script>
@endpush
@endif