@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-alt me-2"></i>Agenda de Pedidos</h2>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Encargo
        </a>
    </div>

    {{-- Filtros rápidos --}}
    <div class="mb-4">
        <span class="badge bg-warning text-dark fs-6 me-2">Pendientes</span>
        <span class="badge bg-success fs-6 me-2">Entregados</span>
    </div>

    <div class="row">
        @forelse ($pedidos as $pedido)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 {{ $pedido->estatus == 'pendiente' ? 'border-start border-4 border-warning' : 'border-start border-4 border-success' }}">
                    
                    {{-- CABECERA CON MENÚ DE OPCIONES --}}
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold">Folio #{{ $pedido->id }}</small>
                            <span class="badge bg-info text-dark ms-1">
                                {{ $pedido->fecha_entrega->format('d/M - h:i A') }}
                            </span>
                        </div>

                        {{-- MENÚ DESPLEGABLE (Solo si está pendiente) --}}
                        @if($pedido->estatus == 'pendiente')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    {{-- Opción Editar --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('pedidos.edit', $pedido->id) }}">
                                            <i class="fas fa-edit text-primary me-2"></i> Editar Pedido
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    {{-- Opción Cancelar --}}
                                    <li>
                                        <form action="{{ route('pedidos.cancelar', $pedido->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de CANCELAR este pedido? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-ban me-2"></i> Cancelar Pedido
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary">{{ $pedido->nombre_cliente }}</h5>
                        <p class="card-text text-muted mb-1">
                            <i class="fas fa-phone me-1"></i> {{ $pedido->telefono_cliente ?? 'Sin teléfono' }}
                        </p>
                        
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
                            @if($pedido->estatus != 'entregado')
                                
                                @if($pedido->saldo_pendiente > 0)
                                    {{-- CASO 1: Debe dinero -> Abre Modal --}}
                                    <button type="button" 
                                            class="btn btn-outline-success w-100 fw-bold" 
                                            onclick="abrirModalCobro(
                                                {{ $pedido->id }}, 
                                                '{{ addslashes($pedido->nombre_cliente) }}', 
                                                '{{ $pedido->id }}', 
                                                {{ $pedido->total }}, 
                                                {{ $pedido->saldo_pendiente }}
                                            )">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Cobrar ${{ number_format($pedido->saldo_pendiente, 0) }} y Entregar
                                    </button>
                                @else
                                    {{-- CASO 2: Pagado -> Entrega Directa --}}
                                    <form action="{{ route('pedidos.cobrar') }}" method="POST" onsubmit="return confirm('Este pedido ya está pagado. ¿Confirmas la entrega?');">
                                        @csrf
                                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                                        <input type="hidden" name="metodo_pago" value="efectivo">
                                        <button class="btn btn-outline-success w-100 fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Entregar (Pagado)
                                        </button>
                                    </form>
                                @endif

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

{{-- VENTANA MODAL DE COBRO --}}
<div class="modal fade" id="modalCobroPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-hand-holding-usd me-2"></i> Finalizar y Entregar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('pedidos.cobrar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="pedido_id" id="modal_pedido_id">
                    
                    <div class="text-center mb-4">
                        <h4 id="modal_cliente_nombre" class="fw-bold text-dark">Cliente</h4>
                        <p class="text-muted" id="modal_folio">Folio #000</p>
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Total del Pedido:</span>
                                <span class="fw-bold" id="modal_total">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-danger fw-bold">Resta por Pagar:</span>
                                <span class="display-6 text-danger fw-bold" id="modal_restante">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="metodo_pago" class="form-label fw-bold">¿Cómo paga el cliente el resto?</label>
                        <select name="metodo_pago" id="metodo_pago" class="form-select form-select-lg" required>
                            <option value="efectivo" selected>💵 Efectivo</option>
                            <option value="tarjeta">💳 Tarjeta de Débito/Crédito</option>
                            <option value="transferencia">📱 Transferencia</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="imprimir_ticket" id="imprimir_ticket" checked>
                        <label class="form-check-label" for="imprimir_ticket">
                            Imprimir Ticket de Entrega
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle me-2"></i> Cobrar y Entregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function abrirModalCobro(id, nombre, folio, total, resta) {
        document.getElementById('modal_pedido_id').value = id;
        document.getElementById('modal_cliente_nombre').textContent = nombre;
        document.getElementById('modal_folio').textContent = 'Folio #' + folio;
        
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        document.getElementById('modal_total').textContent = formatter.format(total);
        document.getElementById('modal_restante').textContent = formatter.format(resta);

        var myModal = new bootstrap.Modal(document.getElementById('modalCobroPedido'));
        myModal.show();
    }

    @if(session('print_ticket'))
        document.addEventListener('DOMContentLoaded', function() {
            const idPedido = "{{ session('print_ticket') }}";
            const urlTicket = "{{ url('/pedidos/ticket') }}/" + idPedido;
            
            let iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = urlTicket;
            document.body.appendChild(iframe);
            
            setTimeout(function() {
                document.body.removeChild(iframe);
            }, 5000);
        });
    @endif
</script>
@endpush