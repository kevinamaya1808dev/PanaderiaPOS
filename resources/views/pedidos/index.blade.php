@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-alt me-2"></i>Agenda de Pedidos</h2>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Encargo
        </a>
    </div>

    {{-- Filtros rápidos (opcional visualmente) --}}
    <div class="mb-4">
        <span class="badge bg-warning text-dark fs-6 me-2">Pendientes</span>
        <span class="badge bg-success fs-6 me-2">Entregados</span>
    </div>

    <div class="row">
        @forelse ($pedidos as $pedido)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 {{ $pedido->estatus == 'pendiente' ? 'border-start border-4 border-warning' : 'border-start border-4 border-success' }}">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Folio #{{ $pedido->id }}</small>
                        
                        {{-- Fecha de Entrega con formato bonito --}}
                        <span class="badge bg-info text-dark">
                            {{ $pedido->fecha_entrega->format('d/M - h:i A') }}
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary">{{ $pedido->nombre_cliente }}</h5>
                        <p class="card-text text-muted mb-1">
                            <i class="fas fa-phone me-1"></i> {{ $pedido->telefono_cliente ?? 'Sin teléfono' }}
                        </p>
                        
                        {{-- Notas especiales destacadas --}}
                        @if($pedido->notas_especiales)
                            <div class="alert alert-light border p-2 mt-2 mb-2 fst-italic small">
                                <i class="fas fa-sticky-note me-1 text-warning"></i> 
                                "{{ Str::limit($pedido->notas_especiales, 50) }}"
                            </div>
                        @endif

                        <hr class="my-2">
                        
                        {{-- Resumen Financiero --}}
                        <div class="d-flex justify-content-between">
                            <span>Total:</span>
                            <span class="fw-bold">${{ number_format($pedido->total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Resta por pagar:</span>
                            <span class="fw-bold">${{ number_format($pedido->saldo_pendiente, 2) }}</span>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 pb-3">
                        <div class="d-grid gap-2">
                            {{-- Botón Entregar (Solo si debe dinero o no está entregado) --}}
                            @if($pedido->estatus != 'entregado')
                                <form action="{{ route('pedidos.entregar', $pedido->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que el cliente ya pagó el saldo restante (${{ $pedido->saldo_pendiente }}) y se lleva el producto?');">
                                    @csrf
                                    <button class="btn btn-outline-success w-100 fw-bold">
                                        @if($pedido->saldo_pendiente > 0)
                                            <i class="fas fa-hand-holding-usd me-1"></i> Cobrar ${{ number_format($pedido->saldo_pendiente, 0) }} y Entregar
                                        @else
                                            <i class="fas fa-check-circle me-1"></i> Marcar Entregado
                                        @endif
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary w-100" disabled>Entregado</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-birthday-cake fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay pedidos pendientes</h4>
                <p>¡Es un buen momento para promocionar pasteles!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection