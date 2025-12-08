@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Encabezado con Nombre del Empleado --}}
    <h2 class="mb-4">
        Nómina de: <strong>{{ $empleado->user->name ?? 'Empleado' }}</strong>
    </h2>

    {{-- Selección de semana --}}
    <form method="GET" class="mb-4 d-flex align-items-end gap-2">
        <div>
            <label class="form-label">Ver semana desde:</label>
            <input type="date" name="fecha"
                class="form-control"
                value="{{ ($inicio ?? now())->format('Y-m-d') }}">
        </div>
        <button class="btn btn-primary">Cambiar Semana</button>
    </form>

    {{-- Rango de fechas de la semana actual --}}
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-calendar-alt me-2"></i>
        Semana del <strong>{{ $inicioSemana->format('d/m/Y') }}</strong>
        al <strong>{{ $finSemana->format('d/m/Y') }}</strong>
    </div>

    {{-- LÓGICA PHP: Calcular Totales Separados (Pagado vs Pendiente) --}}
    @php
        $totalPagado = $pagos->where('liquidado', true)->sum(fn($p) => $p->descuento ? -$p->monto : $p->monto);
        $totalPendiente = $pagos->where('liquidado', false)->sum(fn($p) => $p->descuento ? -$p->monto : $p->monto);
    @endphp

    {{-- TARJETAS DE RESUMEN Y BOTÓN DE LIQUIDAR --}}
    <div class="row mb-4">
        {{-- Lo que ya se pagó (Historial) --}}
        <div class="col-md-6">
            <div class="card bg-light border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted text-uppercase fw-bold">Liquidado anteriormente</small>
                    <h3 class="text-secondary mt-2">${{ number_format($totalPagado, 2) }}</h3>
                </div>
            </div>
        </div>

        {{-- Lo que falta por pagar + Botón de Acción --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 {{ $totalPendiente > 0 ? 'border-start border-4 border-warning' : 'border-start border-4 border-success' }}">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-dark text-uppercase fw-bold">Pendiente por Pagar</small>
                        <h3 class="{{ $totalPendiente > 0 ? 'text-dark' : 'text-success' }} mt-2">
                            ${{ number_format($totalPendiente, 2) }}
                        </h3>
                    </div>

                    {{-- Botón para Marcar como Pagado (Solo si hay deuda) --}}
                    @if($totalPendiente > 0)
                        <form action="{{ route('nomina.liquidar', $empleado->idEmp) }}" method="POST">
                            @csrf
                            {{-- Enviamos la fecha para saber qué semana cerrar --}}
                            <input type="hidden" name="fecha_inicio" value="{{ $inicioSemana->format('Y-m-d') }}">
                            
                            <button type="submit" class="btn btn-success fw-bold shadow">
                                <i class="fas fa-check-double me-1"></i> Marcar Pagado
                            </button>
                        </form>
                    @else
                        <span class="badge bg-success p-2 rounded-pill">
                            <i class="fas fa-check me-1"></i> Al corriente
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE DETALLES --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-bold bg-white py-3">
            <i class="fas fa-list me-1"></i> Desglose de Movimientos
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagos as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $p->concepto ?? '---' }}</td>
                            <td>
                                @if($p->descuento)
                                    <span class="text-danger fw-bold"><i class="fas fa-arrow-down me-1"></i> Descuento</span>
                                @else
                                    <span class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i> Pago</span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                ${{ number_format($p->monto, 2) }}
                            </td>
                            <td class="text-center">
                                @if($p->liquidado)
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                No hay registros esta semana.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FORMULARIO PARA AGREGAR NUEVO PAGO --}}
    <div class="card shadow-sm border-primary">
        <div class="card-header fw-bold text-white bg-primary">
            <i class="fas fa-plus-circle me-1"></i> Registrar Nuevo Movimiento
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('nomina.store', $empleado->idEmp) }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Monto ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" step="0.01" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Concepto</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej: Día Lunes, Bono, Falta...">
                    </div>
                </div>

                <div class="form-check mb-3 bg-light p-2 rounded border">
                    <input class="form-check-input ms-1" type="checkbox" name="descuento" value="1" id="desc">
                    <label class="form-check-label ms-2" for="desc">
                        <strong>¿Es un descuento?</strong> (Restar este monto del total)
                    </label>
                </div>
                <div class="form-check mb-3 bg-light p-2 rounded border">
    <input class="form-check-input ms-1" type="checkbox" name="pagar_de_caja" value="1" id="pagarCaja">
    <label class="form-check-label ms-2 fw-bold text-danger" for="pagarCaja">
        <i class="fas fa-money-bill-wave me-1"></i> Pagar ahora (Descontar de Caja)
    </label>
    <div class="form-text ms-4">Si marcas esto, se creará un egreso en la caja abierta.</div>
</div>

                <div class="d-grid">
                    <button class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i> Guardar Registro
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection