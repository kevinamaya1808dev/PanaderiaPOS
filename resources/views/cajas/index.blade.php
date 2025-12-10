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
        
        {{-- FILA SUPERIOR: INFORMACIÓN Y VENTAS/APARTADOS --}}
        <div class="row mb-4">

            {{-- COLUMNA IZQUIERDA: Panel de Información --}}
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card shadow-lg h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cash-register me-2"></i> Caja Abierta</h4>
                    </div>

                    <div class="card-body">
                        {{-- ... (Info del cajero) ... --}}
                        <p class="mb-1"><strong>ID Caja:</strong> {{ $cajaAbierta->id }}</p>
                        <p class="mb-1"><strong>Cajero:</strong> {{ $cajaAbierta->user->name ?? 'N/A' }}</p>
                        {{-- Aquí dejamos la hora porque es útil saber a qué hora abrió --}}
                        <p class="mb-1"><strong>Apertura:</strong> {{ $cajaAbierta->fecha_hora_apertura->format('d/m/Y H:i') }}</p>
                        <hr>

                        <h5 class="mt-3">Resumen del Turno</h5>

                        {{-- 1. SALDO INICIAL --}}
                        <p class="d-flex justify-content-between mb-1">
                            <span>Saldo Inicial:</span>
                            <span class="badge bg-secondary fs-6">
                                ${{ number_format($cajaAbierta->saldo_inicial, 2) }}
                            </span>
                        </p>

                        {{-- 2. VENTAS --}}
                        <p class="d-flex justify-content-between mb-1 text-success">
                            <span>+ Ventas (Efectivo):</span>
                            <span class="fw-bold">
                                +${{ number_format($ventasEfectivo ?? 0, 2) }}
                            </span>
                        </p>

                        {{-- 3. ANTICIPOS DE APARTADOS --}}
                        <p class="d-flex justify-content-between mb-1 text-success">
                            <span>+ Anticipos (Efectivo):</span>
                            <span class="fw-bold">
                                +${{ number_format($anticiposEfectivo ?? 0, 2) }}
                            </span>
                        </p>

                        {{-- 4. GASTOS --}}
                        <p class="d-flex justify-content-between mb-1 text-danger">
                            <span>- Salidas/Gastos:</span>
                            <span class="fw-bold">
                                -${{ number_format($totalGastos ?? 0, 2) }}
                            </span>
                        </p>

                        <hr>
                        
                        {{-- 5. TOTAL ESTIMADO --}}
                        <h5 class="mt-3">Saldo Actual Estimado:</h5>
                        <div class="display-5 fw-bold text-primary">
                            ${{ number_format($saldoActual ?? 0, 2) }}
                        </div>
                        <small class="text-muted">(Inicial + Ventas + Anticipos - Gastos)</small>
                    </div>

                    {{-- FOOTER DE BOTONES --}}
                    <div class="card-footer">
                        <div class="d-grid gap-2">
                             @if (Auth::user()->hasPermissionTo('cajas', 'mostrar'))
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-file-excel me-2"></i> Exportar Reporte
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="{{ route('cajas.exportar') }}"><i class="fas fa-file-csv me-2 text-success"></i> Excel</a></li>
                                        <li><a class="dropdown-item" href="{{ route('cajas.exportar.pdf') }}"><i class="fas fa-file-pdf me-2 text-danger"></i> PDF</a></li>
                                    </ul>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermissionTo('cajas', 'eliminar'))
                                <form id="form-cerrar-caja" action="{{ route('cajas.cerrar') }}" method="POST">
                                    @csrf
                                    <button type="button" id="btn-cerrar-caja" class="btn btn-danger w-100">
                                            <i class="fas fa-lock me-2"></i> Cerrar Caja
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: VENTAS Y ANTICIPOS --}}
            <div class="col-md-8">
                
                {{-- TABLA 1: VENTAS --}}
                <div class="card shadow-lg mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Ventas del Turno</h4>
                        @php $listaVentas = $ventasDelTurno ?? $ventas ?? collect([]); @endphp
                        <span class="badge bg-white text-info fs-6">
                            Total: ${{ number_format($listaVentas->sum('total'), 2) }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if($listaVentas->isEmpty())
                            <div class="p-4 text-center text-muted">No hay ventas registradas.</div>
                        @else
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped mb-0 align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Método</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($listaVentas as $venta)
                                            <tr>
                                                <td style="font-size: 0.9rem;">
                                                    {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y') }}
                                                </td>
                                                <td class="fw-bold">${{ number_format($venta->total, 2) }}</td>
                                                
                                                {{-- ================= CAMBIOS EN COLUMNA MÉTODO (VENTAS) ================= --}}
                                                <td class="align-middle">
                                                    @php $metodo = strtolower($venta->metodo_pago); @endphp
                                                    @if($metodo == 'tarjeta' || $metodo == 'transferencia')
                                                        {{-- Badge Gris para bancos --}}
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-credit-card me-1"></i> {{ ucfirst($venta->metodo_pago) }}
                                                        </span>
                                                        
                                                        {{-- Mostrar Referencia --}}
                                                        @if($venta->referencia_pago)
                                                            <div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                                                                <i class="fas fa-hashtag"></i> {{ $venta->referencia_pago }}
                                                            </div>
                                                        @else
                                                            <div class="text-danger" style="font-size: 0.7rem;">Sin Ref.</div>
                                                        @endif
                                                    @else
                                                        {{-- Badge Verde para Efectivo --}}
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-money-bill-wave me-1"></i> Efectivo
                                                        </span>
                                                    @endif
                                                </td>
                                                {{-- ================= FIN CAMBIOS ================= --}}

                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="imprimirTicket({{ $venta->id }})">
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

               {{-- TABLA 2: ANTICIPOS --}}
                <div class="card shadow-lg">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-clock me-2"></i> Anticipos / Apartados</h4>
                        <span class="badge bg-white text-dark fs-6">
                            Total: ${{ number_format(isset($anticipos) ? $anticipos->sum('monto') : 0, 2) }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if(!isset($anticipos) || $anticipos->isEmpty())
                            <div class="p-4 text-center text-muted">No hay anticipos registrados en este turno.</div>
                        @else
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Referencia</th> 
                                            <th>Monto</th>
                                            <th>Método</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($anticipos as $anticipo)
                                            <tr>
                                                <td>{{ $anticipo->created_at->format('d/m/Y') }}</td>
                                                <td>
                                                    Pedido #{{ $anticipo->pedido_id }}
                                                </td> 

                                                <td class="fw-bold text-success">
                                                    +${{ number_format($anticipo->monto, 2) }}
                                                </td>

                                                {{-- ================= CAMBIOS EN COLUMNA MÉTODO (ANTICIPOS) ================= --}}
                                                <td class="align-middle">
                                                    @php $metodoAnt = strtolower($anticipo->metodo_pago); @endphp
                                                    @if($metodoAnt == 'tarjeta' || $metodoAnt == 'transferencia')
                                                        <span class="badge bg-secondary">
                                                             <i class="fas fa-credit-card me-1"></i> {{ ucfirst($anticipo->metodo_pago) }}
                                                        </span>
                                                        
                                                        @if($anticipo->referencia_pago)
                                                            <div class="text-muted fw-bold" style="font-size: 0.75rem; margin-top: 2px;">
                                                                Ref: {{ $anticipo->referencia_pago }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-money-bill-wave me-1"></i> {{ ucfirst($anticipo->metodo_pago) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                {{-- ================= FIN CAMBIOS ================= --}}

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div> 

            </div>
        </div> {{-- Fin Fila Superior --}}


        {{-- FILA INFERIOR: GASTOS --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Gastos y Salidas de Efectivo</h4>
                        <span class="badge bg-white text-danger fs-6">Total: ${{ number_format($totalGastos ?? 0, 2) }}</span>
                    </div>

                    <div class="card-body p-0">
                        @if(!isset($gastos) || $gastos->isEmpty())
                            <p class="text-center p-4 text-muted mb-0">No hay gastos registrados en este turno.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4" style="width: 20%;">Fecha</th> 
                                            <th style="width: 60%;">Descripción del Gasto</th>
                                            <th class="text-end pe-4" style="width: 20%;">Monto Retirado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gastos as $gasto)
                                            <tr>
                                                <td class="ps-4 fw-bold text-secondary">
                                                    {{ $gasto->created_at->format('d/m/Y') }}
                                                </td>
                                                <td>{{ $gasto->descripcion }}</td>
                                                <td class="text-end text-danger fw-bold pe-4 fs-5">
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
            </div>
        </div>
        {{-- FIN FILA INFERIOR --}}

    @endif
</div>

{{-- MODALES --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" id="confirmationModalHeader">
                <h5 class="modal-title" id="confirmationModalTitle">Confirmación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">¿Estás seguro?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmationModalConfirmButton">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="alertModalHeader">
                <h5 class="modal-title" id="alertModalTitle">Atención</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="alertModalBody">...</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
                '¿Estás seguro de cerrar la caja? Esta acción no se puede deshacer.',
                'Sí, cerrar caja',
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