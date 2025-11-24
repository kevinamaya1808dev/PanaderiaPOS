@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('historial_cajas.index') }}" class="text-decoration-none text-secondary mb-2 d-inline-block">
                <i class="fas fa-arrow-left"></i> Volver al Historial
            </a>
            <h2>Resumen de Turno #{{ $caja->id }}</h2>
            <span class="badge bg-primary">{{ $nombreTurno }}</span>
            @if(!$caja->fecha_hora_cierre)
                <span class="badge bg-success ms-2">En Curso (Abierto)</span>
            @endif
        </div>
    </div>

    <div class="row mb-4">
        <!-- Columna Izquierda: Información General -->
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">Datos del Empleado</div>
                <div class="card-body text-center">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ strtoupper(substr($caja->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <h5>{{ $caja->user->name ?? 'Usuario Eliminado' }}</h5>
                    <p class="text-muted mb-0">{{ $caja->user->email ?? '' }}</p>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-clock text-success me-2"></i> <strong>Apertura:</strong><br>
                            {{ $caja->fecha_hora_apertura->format('d/m/Y h:i A') }}
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-clock text-danger me-2"></i> <strong>Cierre:</strong><br>
                            {{ $caja->fecha_hora_cierre ? $caja->fecha_hora_cierre->format('d/m/Y h:i A') : 'Turno en curso' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Resumen Financiero -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white fw-bold">Balance del Turno</div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-6"><span class="text-muted fs-5">Saldo Inicial:</span></div>
                        <div class="col-6 text-end fw-bold fs-5">${{ number_format($caja->saldo_inicial, 2) }}</div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <div class="col-6"><span class="text-success fs-5">(+) Ventas Efectivo:</span></div>
                        <div class="col-6 text-end fw-bold text-success fs-5">${{ number_format($totalVentasEfectivo, 2) }}</div>
                    </div>
                    
                    {{-- SE ELIMINÓ LA FILA DE ENTRADAS MANUALES --}}

                    <div class="row mb-3 align-items-center">
                        <div class="col-6"><span class="text-danger fs-5">(-) Salidas/Gastos:</span></div>
                        <div class="col-6 text-end fw-bold text-danger fs-5">- ${{ number_format($egresos, 2) }}</div>
                    </div>
                    <hr class="border-2">
                    <div class="row align-items-center">
                        <div class="col-6"><h3 class="mb-0 text-dark">Total en Caja:</h3><small class="text-muted">(Debe coincidir con físico)</small></div>
                        <div class="col-6 text-end">
                            {{-- CAMBIO: Se quitó + $ingresos de la fórmula --}}
                            @php $calculado = $caja->saldo_inicial + $totalVentasEfectivo - $egresos; @endphp
                            <h2 class="mb-0 fw-bold text-dark">${{ number_format($calculado, 2) }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 1. TABLA DE VENTAS DEL TURNO --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-shopping-cart me-2"></i> Ventas del Turno</span>
            <span class="badge bg-white text-primary">{{ $ventas->count() }} Registros</span>
        </div>
        <div class="card-body p-0">
            @if($ventas->isEmpty())
                <p class="text-center p-4 text-muted">No se registraron ventas en este turno.</p>
            @else
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventas as $venta)
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y') }}</td>
                                    <td>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($venta->detalles as $detalle)
                                                <li>{{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? '?' }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="fw-bold">${{ number_format($venta->total, 2) }}</td>
                                    <td>
                                        @if($venta->metodo_pago == 'pendiente')
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @else
                                            <span class="badge bg-success text-white">{{ ucfirst($venta->metodo_pago) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="imprimirTicket({{ $venta->id }})" title="Reimprimir Ticket">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- 2. TABLA DE GASTOS (Solo Egresos) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="fas fa-money-bill-wave me-2"></i> Gastos del Turno
        </div>
        <div class="card-body p-0">
            {{-- Filtramos solo los egresos --}}
            @php $gastos = $movimientos->where('tipo', 'egreso'); @endphp

            @if($gastos->isEmpty())
                <p class="text-center p-4 text-muted">No hay gastos registrados.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Fecha</th>
                                <th>Descripción</th>
                                <th class="text-end pe-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastos as $mov)
                                <tr>
                                    <td class="ps-4">{{ $mov->created_at->format('d/m/y') }}</td>
                                    <td>{{ $mov->descripcion ?? 'N/A' }}</td>
                                    <td class="text-end pe-4 fw-bold text-danger">
                                        - ${{ number_format($mov->monto, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Iframe para impresión --}}
<iframe id="ticket-print-frame" name="ticket-print-frame" style="display: none;"></iframe>

<style>
    svg.svg-inline--fa { height: 1em; width: auto; }
    .fas, .far, .fab { font-size: 1em; }
</style>
@endsection

@push('scripts')
<script>
    function imprimirTicket(ventaId) {
        const url = `{{ url('/ventas/ticket/html/') }}/${ventaId}`;
        let iframe = document.getElementById('ticket-print-frame');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'ticket-print-frame';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;
        iframe.onload = function() {
            try { iframe.contentWindow.print(); } catch (e) { console.error(e); }
            iframe.src = 'about:blank';
            iframe.onload = null;
        };
    }
</script>
@endpush