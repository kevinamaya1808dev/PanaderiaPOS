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
    {{-- CASO 1: CAJA CERRADA --}}
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
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('saldo_inicial') is-invalid @enderror"
                                       id="saldo_inicial" name="saldo_inicial"
                                       value="{{ old('saldo_inicial', 0.00) }}" required>
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
    {{-- CASO 2: CAJA ABIERTA --}}
    {{-- ========================================================== --}}
    @else
        <div class="row">

            {{-- Panel de Información --}}
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

                        {{-- 1. SALDO INICIAL --}}
                        <p class="d-flex justify-content-between mb-1">
                            <span>Saldo Inicial:</span>
                            <span class="badge bg-secondary fs-6">
                                ${{ number_format($cajaAbierta->saldo_inicial, 2) }}
                            </span>
                        </p>

                        {{-- 2. VENTAS --}}
                        <p class="d-flex justify-content-between mb-1 text-success">
                            <span>+ Ventas en Efectivo:</span>
                            <span class="fw-bold">
                                {{-- Usamos variable del controller o fallback a 0 --}}
                                +${{ number_format($ventasEfectivo ?? $totalVentasEfectivo ?? 0, 2) }}
                            </span>
                        </p>

                        {{-- 3. GASTOS (NUEVO) --}}
                        <p class="d-flex justify-content-between mb-1 text-danger">
                            <span>- Salidas/Gastos:</span>
                            <span class="fw-bold">
                                -${{ number_format($totalGastos ?? 0, 2) }}
                            </span>
                        </p>

                        <hr>
                        
                        {{-- 4. TOTAL ESTIMADO --}}
                        <h5 class="mt-3">Saldo Actual Estimado:</h5>
                        <div class="display-5 fw-bold text-primary">
                            {{-- Variable $saldoActual calculada en el Controller --}}
                            ${{ number_format($saldoActual ?? 0, 2) }}
                        </div>
                        <small class="text-muted">(Saldo Inicial + Ventas - Gastos)</small>
                    </div>

                    {{-- FOOTER DE BOTONES --}}
                    <div class="card-footer">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch gap-3">

                            {{-- BOTÓN EXPORTAR --}}
                            @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
                                <div class="btn-group w-100 w-md-auto">
                                    <button type="button" class="btn btn-success btn-lg dropdown-toggle w-100"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-file-excel me-2"></i> Exportar Reporte
                                    </button>
                                    <ul class="dropdown-menu w-100">
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

                            {{-- BOTÓN CERRAR CAJA --}}
                            @if (Auth::user()->hasPermissionTo('cajas', 'eliminar'))
                                <form id="form-cerrar-caja" action="{{ route('cajas.cerrar') }}"
                                      method="POST" class="w-100 w-md-auto m-0">
                                    @csrf
                                    <button type="button" id="btn-cerrar-caja"
                                            class="btn btn-danger btn-lg w-100">
                                            <i class="fas fa-lock me-2"></i> Cerrar Caja
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>

                </div>
            </div>

            {{-- Columna Derecha: Tablas --}}
            <div class="col-md-7 mb-4">
                
                {{-- 1. TABLA VENTAS --}}
                <div class="card shadow-lg h-100 mb-4"> {{-- Agregué mb-4 para espacio --}}
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Ventas del Turno</h4>
                    </div>

                    <div class="card-body p-0">
                        {{-- Ajuste de variable: soporte para $ventasDelTurno (tu codigo viejo) o $ventas (mi codigo nuevo) --}}
                        @php $listaVentas = $ventasDelTurno ?? $ventas ?? collect([]); @endphp
                        
                        @if($listaVentas->isEmpty())
                            <p class="text-center p-3">No hay ventas registradas en este turno.</p>
                        @else
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Productos</th>
                                            <th>Total</th>
                                            <th>Método</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($listaVentas as $venta)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y') }}</td>
                                                <td>
                                                    <ul class="list-unstyled mb-0" style="font-size: 0.9em;">
                                                        @foreach($venta->detalles as $detalle)
                                                            <li>{{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td>${{ number_format($venta->total, 2) }}</td>
                                                <td>
                                                    @if($venta->metodo_pago == 'efectivo')
                                                        <span class="badge bg-success">Efectivo</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($venta->metodo_pago) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                            onclick="imprimirTicket({{ $venta->id }})">
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

                {{-- 2. TABLA GASTOS (NUEVO) --}}
                <div class="card shadow-lg h-100">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Gastos/Salidas</h4>
                    </div>

                    <div class="card-body p-0">
                        @if(!isset($gastos) || $gastos->isEmpty())
                            <p class="text-center p-3">No hay gastos registrados en este turno.</p>
                        @else
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Hora</th>
                                            <th>Descripción</th>
                                            <th class="text-end pe-3">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gastos as $gasto)
                                            <tr>
                                                <td class="ps-3">{{ $gasto->created_at->format('H:i') }}</td>
                                                <td>{{ $gasto->descripcion }}</td>
                                                <td class="text-end text-danger fw-bold pe-3">
                                                    - ${{ number_format($gasto->monto, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div> 
                {{-- FIN TABLA GASTOS --}}

            </div> </div>
    @endif
</div>

{{-- ========================================================== --}}
{{-- MODALES --}}
{{-- ========================================================== --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" id="confirmationModalHeader">
                <h5 class="modal-title" id="confirmationModalTitle">Confirmación</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">¿Estás seguro?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger"
                        id="confirmationModalConfirmButton">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="alertModalHeader">
                <h5 class="modal-title" id="alertModalTitle">Atención</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="alertModalBody">...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let alertModal, confirmationModal;

    document.addEventListener('DOMContentLoaded', function() {
        alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

        document.getElementById('btn-cerrar-caja')?.addEventListener('click', () => {
            showConfirmationModal(
                'Cerrar Caja',
                '¿Estás seguro de cerrar la caja?',
                'Sí, cerrar',
                'btn-danger',
                () => document.getElementById('form-cerrar-caja').submit()
            );
        });
    });

    function showAlertModal(body, title = 'Atención', type = 'danger') {
        document.getElementById('alertModalBody').textContent = body;
        document.getElementById('alertModalTitle').textContent = title;

        const header = document.getElementById('alertModalHeader');
        header.className = 'modal-header text-white ' + (type === 'danger' ? 'bg-danger' : 'bg-warning');

        alertModal.show();
    }

    function showConfirmationModal(title, body, confirmText, confirmClass, callback) {
        document.getElementById('confirmationModalTitle').textContent = title;
        document.getElementById('confirmationModalBody').textContent = body;

        const oldBtn = document.getElementById('confirmationModalConfirmButton');
        const newBtn = oldBtn.cloneNode(true);
        newBtn.textContent = confirmText;
        newBtn.className = 'btn ' + confirmClass;

        oldBtn.parentNode.replaceChild(newBtn, oldBtn);

        newBtn.addEventListener('click', () => {
            confirmationModal.hide();
            callback();
        });

        confirmationModal.show();
    }

    function imprimirTicket(id) {
        const url = `{{ url('/ventas/ticket/html/') }}/${id}`;

        let iframe = document.getElementById('ticket-print-frame');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'ticket-print-frame';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }

        iframe.src = url;

        iframe.onload = () => iframe.contentWindow.print();
        iframe.onerror = () => showAlertModal("No se pudo cargar el ticket.");
    }
</script>
@endpush