@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gestión de Flujo de Caja</h2>

    {{-- Mensajes de Sesión --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

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
                
                {{-- Solo mostrar si el usuario tiene permiso de ALTA en el módulo 'cajas' --}}
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
                        <p class="mb-1"><strong>Saldo Inicial:</strong> <span class="badge bg-secondary fs-6">${{ number_format($cajaAbierta->saldo_inicial, 2) }}</span></p>
                        <hr>
                        <h5 class="mt-3">Saldo Actual Calculado:</h5>
                        <div class="display-5 fw-bold text-success">${{ number_format($saldoActual, 2) }}</div>
                        <small class="text-muted">(Incluye saldo inicial y movimientos manuales. Ventas no incluidas aún.)</small>
                    </div>
                    <div class="card-footer d-grid">
                        {{-- Botón de Cierre (Solo visible si tiene permiso de 'eliminar' en 'cajas') --}}
                        @if (Auth::user()->hasPermissionTo('cajas', 'eliminar'))
                            <form action="{{ route('cajas.cerrar') }}" method="POST" onsubmit="return confirm('¿Estás seguro de cerrar la caja? Esta acción no se puede deshacer.');">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-lock me-2"></i> Cerrar Caja
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning text-center py-2 mb-0">No tienes permiso para cerrar la caja.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Historial de Movimientos --}}
            <div class="col-md-7 mb-4">
                <div class="card shadow-lg h-100">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Movimientos Manuales</h4>
                        {{-- Aquí iría el botón para añadir movimiento, protegido por 'cajas', 'alta' --}}
                    </div>
                    <div class="card-body p-0">
                        <div style="max-height: 400px; overflow-y: auto;">
                            @if ($movimientos->isEmpty())
                                <div class="alert alert-light text-center m-3">No hay movimientos manuales registrados.</div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach ($movimientos as $movimiento)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <i class="fas {{ $movimiento->tipo === 'ingreso' ? 'fa-arrow-circle-up text-success' : 'fa-arrow-circle-down text-danger' }} me-2"></i>
                                                {{ $movimiento->descripcion }} 
                                                <small class="text-muted d-block">{{ $movimiento->created_at->format('H:i') }} - {{ ucfirst($movimiento->metodo_pago) }}</small>
                                            </div>
                                            <span class="fw-bold fs-6 {{ $movimiento->tipo === 'ingreso' ? 'text-success' : 'text-danger' }}">
                                                {{ $movimiento->tipo === 'egreso' ? '-' : '+' }}${{ number_format($movimiento->monto, 2) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection