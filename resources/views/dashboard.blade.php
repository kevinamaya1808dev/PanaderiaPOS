@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Dashboard Principal</h2>
    <p class="lead text-secondary">
        Bienvenido, {{ Auth::user()->name }}. Tu cargo actual es:
        <span class="badge bg-info text-dark">{{ Auth::user()->cargo->nombre ?? 'N/A' }}</span>.
    </p>

    {{-- =============================================== --}}
    {{-- ¡NUEVO! SECCIÓN DE GRÁFICAS --}}
    {{-- =============================================== --}}
    @if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
    <h4 class="mt-5 mb-3">Análisis de Ventas (Este Año)</h4>
    <div class="row">
        <!-- Gráfica 1: Ventas por Mes -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    Ventas Totales por Mes (Haz clic en una barra para filtrar productos)
                </div>
                <div class="card-body">
                    <canvas id="ventasMesChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica 2: Top 5 Productos -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    Top 5 Productos Vendidos
                </div>
                <div class="card-body">
                    <!-- Este h5 cambiará dinámicamente -->
                    <h5 id="topProductosTitulo" class="text-center mb-3">Todo el Año</h5>
                    <canvas id="topProductosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    {{-- =============================================== --}}
    {{-- FIN SECCIÓN DE GRÁFICAS --}}
    {{-- =============================================== --}}
@endif

    <h4 class="mt-5 mb-3">Accesos Directos a Módulos</h4>
    
    {{-- TU CÓDIGO ACTUAL DE ACCESOS DIRECTOS (SIN CAMBIOS) --}}
    <div class="row row-cols-1 row-cols-md-3 g-4">
        {{-- =============================================== --}}
        {{-- ACCESOS PARA CAJERO (Y OTROS ROLES CON PERMISO) --}}
        {{-- =============================================== --}}
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
        @endif
        {{-- =============================================== --}}
        {{-- ACCESOS SOLO PARA ADMINISTRADORES --}}
        {{-- =============================================== --}}
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
        {{-- Módulo 7: Cobrar Ventas Pendientes --}}
@if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
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
    </div>
</div>
@endsection

{{-- =============================================== --}}
{{-- ¡NUEVO! SCRIPT PARA LAS GRÁFICAS --}}
{{-- =============================================== --}}
@if (Auth::user()->hasPermissionTo('cargos', 'mostrar'))
@push('scripts')
<!-- 1. Importar la librería Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- 2. Nuestro script para crear las gráficas -->
<script>
    // Variables globales para guardar las instancias de las gráficas
    let ventasChart;
    let topProductosChart;

    // Nombres de los meses (para las etiquetas)
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    // Obtener los 'contextos' de los canvas
    const ctxVentas = document.getElementById('ventasMesChart').getContext('2d');
    const ctxProductos = document.getElementById('topProductosChart').getContext('2d');
    const topProductosTitulo = document.getElementById('topProductosTitulo');

    /**
     * Función principal que pide los datos a nuestra API
     * y llama a las funciones que dibujan las gráficas.
     * 'mes' (ej. 1 para Enero) es opcional.
     */
    async function cargarDatos(mes = null) {
        
        let url = '/dashboard-data'; // La ruta que definiremos en api.php
        if (mes) {
            url += `?month=${mes}`; // Si pasamos un mes, lo añadimos a la URL
        }

        try {
            const response = await fetch(url);
            const data = await response.json();

            // Solo dibujamos la gráfica de ventas la primera vez
            if (!mes) {
                renderVentasChart(data.ventas_por_mes);
            }
            
            // Dibujamos la gráfica de productos (se actualiza cada vez)
            renderTopProductosChart(data.top_productos);

            // Actualizar el título
            topProductosTitulo.innerText = mes ? `Mes: ${meses[mes-1]}` : 'Todo el Año';

        } catch (error) {
            console.error('Error al cargar datos del dashboard:', error);
        }
    }

    /**
     * Dibuja la gráfica de Ventas por Mes
     */
    function renderVentasChart(dataVentas) {
        // Preparamos los datos: un array de 12 ceros
        const valores = new Array(12).fill(0);
        
        // Llenamos el array con los datos de la API
        dataVentas.forEach(item => {
            // item.mes es 1-12, lo ajustamos a 0-11 para el array
            valores[item.mes - 1] = item.total_ventas;
        });

        // Destruimos la gráfica anterior si existe (para evitar errores)
        if (ventasChart) {
            ventasChart.destroy();
        }

        // Creamos la nueva gráfica
        ventasChart = new Chart(ctxVentas, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas Totales ($)',
                    data: valores,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                },
                // --- ¡AQUÍ ESTÁ LA MAGIA INTERACTIVA! ---
                onClick: (e) => {
                    const elements = ventasChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (elements.length > 0) {
                        const clickedIndex = elements[0].index; // 0 para Ene, 1 para Feb...
                        const mesSeleccionado = clickedIndex + 1; // 1 para Ene, 2 para Feb...
                        
                        // Volvemos a llamar a cargarDatos, pero SÓLO para ese mes
                        cargarDatos(mesSeleccionado);
                    }
                }
            }
        });
    }

    /**
     * Dibuja la gráfica de Top 5 Productos
     */
    function renderTopProductosChart(dataProductos) {
        // Preparamos los datos
        const labels = dataProductos.map(p => p.nombre);
        const valores = dataProductos.map(p => p.total_cantidad);

        // Destruimos la gráfica anterior
        if (topProductosChart) {
            topProductosChart.destroy();
        }

        // Creamos la nueva gráfica (horizontal)
        topProductosChart = new Chart(ctxProductos, {
            type: 'bar', // 'bar' normal
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cantidad Vendida',
                    data: valores,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // <-- Esto la hace horizontal
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }

    // --- Carga inicial de datos al abrir la página ---
    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos(); // Primera carga (todo el año)
    });

</script>
@endpush
@endif