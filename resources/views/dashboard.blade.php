@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dashboard Principal</h2>
    <p class="lead text-secondary">
        Bienvenido, {{ Auth::user()->name }}. Tu cargo actual es:
        <span class="badge bg-info text-dark">{{ Auth::user()->cargo->nombre ?? 'N/A' }}</span>.
    </p>

    {{-- =============================================== --}}
    {{-- MÉTRICAS FINANCIERAS (SEMANAL Y MENSUAL) --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
        <h3 class="mb-3 text-secondary mt-4">Métricas Financieras</h3>

        {{-- FILA 1: SEMANA --}}
        <h5 class="mb-3">Esta Semana <small class="text-muted fs-6">({{ \Carbon\Carbon::now()->startOfWeek()->format('d M') }} - {{ \Carbon\Carbon::now()->endOfWeek()->format('d M') }})</small></h5>
        <div class="row mb-4">
            <!-- Ingresos Semanales -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ingresos Semanales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['ingresos'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300 text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costos Semanales -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Costos Semanales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['costos'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dolly fa-2x text-gray-300 text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilidad Semanal -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Utilidad Semanal</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['weekly']['utilidad'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300 text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILA 2: MES --}}
        <h5 class="mb-3">Este Mes <small class="text-muted fs-6">({{ \Carbon\Carbon::now()->format('F Y') }})</small></h5>
        <div class="row mb-5">
            <!-- Ingresos Mensuales -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ingresos Mensuales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['ingresos'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300 text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costos Mensuales -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Costos Mensuales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['costos'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box-open fa-2x text-gray-300 text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilidad Mensual -->
            <div class="col-md-4 mb-3">
                <div class="card border-start border-4 border-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Utilidad Mensual</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($metrics['monthly']['utilidad'], 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sack-dollar fa-2x text-gray-300 text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE GRÁFICAS --}}
        <h4 class="mt-5 mb-3">Análisis de Negocio (Este Año)</h4>
        <div class="row">
            <!-- Gráfica 1: Ventas vs Utilidad -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Comparativa: Ventas vs Utilidad Real</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="ventasMesChart" height="120"></canvas>
                        </div>
                        <small class="text-muted mt-2 d-block text-center">Haz clic en una barra para filtrar los productos vendidos en ese mes.</small>
                    </div>
                </div>
            </div>

            <!-- Gráfica 2: Top Productos -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Top 5 Productos Vendidos</h6>
                    </div>
                    <div class="card-body">
                        <h5 id="topProductosTitulo" class="text-center mb-3 fw-bold">Todo el Año</h5>
                        <div class="chart-area">
                            <canvas id="topProductosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =============================================== --}}
    {{-- ACCESOS DIRECTOS (Tus botones originales) --}}
    {{-- =============================================== --}}
    <h4 class="mt-5 mb-3">Accesos Directos a Módulos</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        {{-- Módulo 1: Punto de Venta (POS) --}}
        @if (Auth::user()->hasPermissionTo('ventas', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="fas fa-cash-register me-2"></i> Punto de Venta (POS)</h5>
                        <p class="card-text">Inicia una nueva venta, escanea productos y procesa los pagos de los clientes.</p>
                        <a href="{{ route('ventas.tpv') }}" class="btn btn-sm btn-outline-success">Ir a Ventas →</a>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Módulo 2: Flujo de Caja --}}
        @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="fas fa-dollar-sign me-2"></i> Flujo de Caja</h5>
                        <p class="card-text">Realiza aperturas, cierres y consulta los movimientos de caja del día.</p>
                        <a href="{{ route('cajas.index') }}" class="btn btn-sm btn-outline-success">Ir a Caja →</a>
                    </div>
                </div>
            </div>
            {{-- Módulo 7: Cobrar Ventas Pendientes --}}
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="fas fa-file-invoice-dollar me-2"></i> Cobrar Pendientes</h5>
                        <p class="card-text">Busca un ticket pendiente por su folio y registra el pago final en caja.</p>
                        <a href="{{ route('cobrar.index') }}" class="btn btn-sm btn-outline-warning">Ir a Cobrar →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo 3: Categorías (Módulo: productos) --}}
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
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-box me-2"></i> Catálogo de Productos</h5>
                        <p class="card-text">Gestiona el inventario, precios y detalles de tus productos.</p>
                        <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-primary">Ir a Productos →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Módulo 4: Empleados (Módulo: usuarios) --}}
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

        {{-- Módulo 5: Cargos y Permisos (Módulo: cargos) --}}
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
        
        @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
    <div class="col">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title text-secondary"><i class="fas fa-history me-2"></i> Historial de Turnos</h5>
                <p class="card-text">Consulta los cortes de caja pasados, turnos matutinos y vespertinos.</p>
                <a href="{{ route('historial_cajas.index') }}" class="btn btn-sm btn-outline-secondary">Ver Historial →</a>
            </div>
        </div>
    </div>
@endif
    </div>
</div>
@endsection

{{-- JAVASCRIPT PARA LAS GRÁFICAS --}}
@if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables globales para las gráficas
    let ventasChart; 
    let topProductosChart;

    // Etiquetas de meses
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    // Contextos de Canvas
    const ctxVentas = document.getElementById('ventasMesChart').getContext('2d');
    const ctxProductos = document.getElementById('topProductosChart').getContext('2d');
    const topProductosTitulo = document.getElementById('topProductosTitulo');

    /**
     * Carga los datos desde la API
     */
    async function cargarDatos(mes = null) {
        let url = '/dashboard-data'; // Ruta en web.php
        if (mes) {
            url += `?month=${mes}`;
        }

        try {
            const response = await fetch(url);
            const data = await response.json();

            // Si no se especifica mes, dibujamos la gráfica principal (Todo el año)
            if (!mes) {
                renderVentasChart(data.datos_por_mes);
            }
            
            // Siempre dibujamos la gráfica de productos (se filtra si hay mes)
            renderTopProductosChart(data.top_productos);

            // Actualizar el título dinámicamente
            topProductosTitulo.innerText = mes ? `Mes: ${meses[mes-1]}` : 'Todo el Año';

        } catch (error) {
            console.error('Error al cargar datos del dashboard:', error);
        }
    }

    /**
     * Dibuja la gráfica COMPARATIVA (Ventas vs Utilidad)
     */
    function renderVentasChart(datosMes) {
        const ventas = new Array(12).fill(0);
        const utilidad = new Array(12).fill(0);
        
        // Llenar arrays
        datosMes.forEach(item => {
            // item.mes es 1-12, array es 0-11
            ventas[item.mes - 1] = item.total_ventas;
            utilidad[item.mes - 1] = item.total_utilidad;
        });

        if (ventasChart) {
            ventasChart.destroy();
        }

        ventasChart = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Ventas Totales ($)',
                        data: ventas,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Azul
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Utilidad Neta ($)',
                        data: utilidad,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)', // Verde
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                },
                // Interactividad: Clic en barra filtra productos
                onClick: (e) => {
                    const elements = ventasChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (elements.length > 0) {
                        const clickedIndex = elements[0].index;
                        const mesSeleccionado = clickedIndex + 1;
                        cargarDatos(mesSeleccionado);
                    }
                }
            }
        });
    }

    /**
     * Dibuja la gráfica de Top Productos
     */
    function renderTopProductosChart(dataProductos) {
        const labels = dataProductos.map(p => p.nombre);
        const valores = dataProductos.map(p => p.total_cantidad);

        if (topProductosChart) {
            topProductosChart.destroy();
        }

        topProductosChart = new Chart(ctxProductos, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Piezas Vendidas',
                    data: valores,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }

    // Carga inicial al abrir la página
    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos(); 
    });

</script>
@endpush
@endif