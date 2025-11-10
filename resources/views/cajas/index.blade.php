@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gestión de Flujo de Caja</h2>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error de Validación:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- CASO 1: CAJA CERRADA (Mostrar Formulario de Apertura) --}}
    {{-- ========================================================== --}}
    @if (!$cajaAbierta)
        <div class="card shadow-lg mx-auto" style="max-width: 500px;">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-money-check-alt me-2"></i> Abrir Caja del Día</h4>
            </div>
            <div class="card-body">
                <p>No tienes una caja abierta. Registra el saldo inicial para empezar las ventas.</p>
                
                @if (Auth::user()->hasPermissionTo('cajas', 'alta'))
                    <form action="{{ route('cajas.abrir') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="saldo_inicial" class="form-label">Saldo Inicial (Fondo de Caja)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('saldo_inicial') is-invalid @enderror" 
                                       id="saldo_inicial" name="saldo_inicial" value="{{ old('saldo_inicial', 0.00) }}" required>
                                @error('saldo_inicial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-box-open me-2"></i> Abrir Caja
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning">No tienes permiso para abrir una caja.</div>
                @endif
            </div>
        </div>

    {{-- ========================================================== --}}
    {{-- CASO 2: CAJA ABIERTA (Mostrar Estado y Movimientos) --}}
    {{-- ========================================================== --}}
    @else
        <div class="row">
            {{-- Panel de Información General y Cierre --}}
            <div class="col-md-5 mb-4">
                <div class="card shadow-lg h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cash-register me-2"></i> Caja Abierta (Turno)</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>ID Caja:</strong> {{ $cajaAbierta->id }}</p>
                        <p class="mb-1"><strong>Cajero:</strong> {{ $cajaAbierta->user->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Apertura:</strong> {{ $cajaAbierta->fecha_hora_apertura->format('d/m/Y H:i') }}</p>
                        <hr> 
                        <h5 class="mt-3">Resumen del Turno Actual</h5>
                        <p class="d-flex justify-content-between mb-1">
                            <span>Saldo Inicial:</span> 
                            <span class="badge bg-secondary fs-6">${{ number_format($cajaAbierta->saldo_inicial, 2) }}</span>
                        </p>
                        
                        <p class="d-flex justify-content-between mb-1 text-success">
                            <span>+ Ventas en Efectivo:</span> 
                            <span class="fw-bold">${{ number_format($ventasEfectivo ?? 0, 2) }}</span>
                        </p>

                        @php 
                            $saldoMovimientos = $movimientos->sum(function($m){ return $m->tipo === 'ingreso' ? $m->monto : -$m->monto; });
                        @endphp
                        <p class="d-flex justify-content-between mb-1 {{ $saldoMovimientos >= 0 ? 'text-info' : 'text-danger' }}">
                            <span>+/- Movimientos Manuales:</span>
                            <span class="fw-bold">
                                {{ $saldoMovimientos >= 0 ? '+' : '-' }}${{ number_format(abs($saldoMovimientos), 2) }}
                            </span>
                        </p>
                        
                        <hr>
                        <h5 class="mt-3">Saldo Actual Estimado:</h5>
                        <div class="display-5 fw-bold text-primary">${{ number_format($saldoActual, 2) }}</div> 
                        <small class="text-muted">(Saldo Inicial + Ventas Efectivo +/- Mov. Manuales)</small> 
                    </div>
                    
                    {{-- FOOTER DE BOTONES CORREGIDO Y ACTUALIZADO  --}}
                    <div class="card-footer d-flex justify-content-between p-3">

                        {{-- 1. Botón de Registrar Movimiento --}}
                        @if (Auth::user()->hasPermissionTo('cajas', 'editar'))
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#movimientoModal">
                                <i class="fas fa-exchange-alt me-2"></i> Movimiento
                            </button>
                        @endif

                        {{-- 2. BOTÓN DESPLEGABLE DE REPORTE --}}
                        @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-excel me-2"></i> Exportar Reporte
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('cajas.exportar') }}">
                                            <i class="fas fa-file-csv me-2 text-success"></i> Excel 
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('cajas.exportar.pdf') }}">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i> PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif

                        {{-- 3. Botón de Cierre --}}
                        @if (Auth::user()->hasPermissionTo('cajas', 'eliminar'))
                            <form action="{{ route('cajas.cerrar') }}" method="POST" onsubmit="return confirm('¿Estás seguro de cerrar la caja? Se registrará un movimiento con el total de ventas en efectivo.');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-lock me-2"></i> Cerrar Caja
                                </button>
                            </form>
                        @endif
                        
                    </div>
                    {{-- ***** FIN DEL BLOQUE DE BOTONES ***** --}}
                </div>
            </div>

            {{-- Historial de Movimientos--}}
            <div class="col-md-7 mb-4">
                <div class="card shadow-lg h-100">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Movimientos Registrados en Caja</h4>
                    </div>
                    <div class="card-body p-0">
                        
                        @if($ventasDelTurno->isEmpty() && $movimientos->isEmpty())
                            <p class="text-center p-3">No hay ventas ni movimientos manuales registrados en este turno.</p>
                        
                        @else
                            
                            <!-- 1. Tabla de Ventas del Turno -->
                            @if($ventasDelTurno->isNotEmpty())
                                <h5 class="card-title pt-2 pb-2 border-bottom">Ventas del Turno</h5>
                                
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Productos</th>
                                                <th>Total</th>
                                                <th>Método de Pago</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    @foreach($ventasDelTurno as $venta)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y') }}</td>
                                            <td>
                                                <ul class="list-unstyled mb-0" style="font-size: 0.9em;">
                                                    @foreach($venta->detalles as $detalle)
                                                        <li>
                                                            {{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? 'Producto no encontrado' }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td>${{ number_format($venta->total, 2) }}</td>
                                            <td>{{ ucfirst($venta->metodo_pago) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="imprimirTicket({{ $venta->id }})" 
                                                        title="Reimprimir Ticket">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- 2. Tabla de Movimientos Manuales -->
                            @if($movimientos->isNotEmpty())
                                <h5 class="card-title pt-3 pb-2 border-bottom">Movimientos Manuales</h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th>Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($movimientos as $mov)
                                                <tr class="{{ $mov->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                                                    <td>{{ $mov->created_at->format('d/m/y') }}</td>
                                                    <td>{{ ucfirst($mov->tipo) }}</td>
                                                    <td>{{ $mov->descripcion ?? 'N/A' }}</td>
                                                    <td>
                                                        {{ $mov->tipo == 'ingreso' ? '+' : '-' }}
                                                        ${{ number_format($mov->monto, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


{{-- ========================================================== --}}
        {{-- Modal para Registrar Movimiento --}}
{{-- ========================================================== --}}
@if ($cajaAbierta)
<div class="modal fade" id="movimientoModal" tabindex="-1" aria-labelledby="movimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cajas.movimiento') }}" method="POST">
                @csrf
                <input type="hidden" name="caja_id" value="{{ $cajaAbierta->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="movimientoModalLabel">Registrar Movimiento Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    {{-- Tipo de Movimiento --}}
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="egreso">Salida de Efectivo (Gasto, Retiro)</option>
                            <option value="ingreso">Entrada de Efectivo (Ingreso, Fondo)</option>
                        </select>
                    </div>

                    {{-- Monto (Siempre positivo) --}}
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="monto" name="monto" required placeholder="0.00">
                        </div>
                    </div>

                    {{-- Descripción (Motivo) --}}
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (Motivo)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ej: Pago a proveedor de agua, Añadir cambio..." required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    /**
     * Función para imprimir un ticket (VERSIÓN CORREGIDA)
     */
    function imprimirTicket(ventaId) {
        // 1. Construir la URL del ticket
        const url = `{{ url('/ventas/ticket/html/') }}/${ventaId}`;

        // 2. Obtener o crear el iframe
        let iframe = document.getElementById('ticket-print-frame');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'ticket-print-frame';
            iframe.style.display = 'none'; // Sigue siendo invisible
            document.body.appendChild(iframe);
        }

        // 3. Asignar la URL al iframe
        iframe.src = url;

        // 4. Esperar a que el iframe cargue el contenido HTML
        iframe.onload = function() {
            try {
                // 5. Enviar el comando de impresión al iframe
                iframe.contentWindow.print();
            } catch (e) {
                console.error("Error al intentar imprimir el iframe:", e);
                alert("Hubo un error al preparar la impresión.");
            }
        };

        // 7. (Manejo de error) Si el iframe no carga
        iframe.onerror = function() {
            console.error("Error: No se pudo cargar el ticket en el iframe. URL: " + url);
            alert("Error al cargar el ticket. Verifique la conexión.");
        };
    }
</script>
@endpush