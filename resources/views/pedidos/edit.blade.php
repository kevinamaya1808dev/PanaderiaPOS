@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Editando Pedido #{{ $pedido->id }}</h4>
                </div>
                
                <div class="card-body">
                    {{-- El formulario apunta a la ruta UPDATE con método PUT --}}
                    <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST" id="form-editar-pedido">
                        @csrf
                        @method('PUT')

                        {{-- SECCIÓN 1: DATOS DEL CLIENTE --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cliente</label>
                                <input type="text" name="nombre_cliente" class="form-control" value="{{ $pedido->nombre_cliente }}" required>
                                {{-- Si usas IDs de clientes para relacionar, mantén este input oculto --}}
                                <input type="hidden" name="cliente_id" value="{{ $pedido->cliente_id }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Teléfono</label>
                                <input type="text" name="telefono_cliente" class="form-control" value="{{ $pedido->telefono_cliente }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha y Hora de Entrega</label>
                                {{-- Formato para input datetime-local: Y-m-d\TH:i --}}
                                <input type="datetime-local" name="fecha_entrega" class="form-control" 
                                       value="{{ $pedido->fecha_entrega->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Notas Especiales</label>
                                <input type="text" name="notas_especiales" class="form-control" value="{{ $pedido->notas_especiales }}">
                            </div>
                        </div>

                        <hr>

                        {{-- SECCIÓN 2: PRODUCTOS --}}
                        <h5 class="mb-3 text-primary"><i class="fas fa-box-open me-2"></i>Productos del Pedido</h5>
                        
                        {{-- Contenedor donde JavaScript dibujará los productos existentes y nuevos --}}
                        <div id="productos-container">
                            {{-- Se llena con JS al cargar la página --}}
                        </div>

                        {{-- Botón para agregar más productos --}}
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarFilaProducto()">
                                <i class="fas fa-plus"></i> Agregar Otro Producto
                            </button>
                        </div>

                        {{-- SECCIÓN 3: TOTALES --}}
                        <div class="alert alert-light border">
                            <div class="d-flex justify-content-between fs-5">
                                <span>Nuevo Total Estimado:</span>
                                <strong id="total-display">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between text-success">
                                <span>Anticipo (Ya pagado):</span>
                                <strong>- ${{ number_format($pedido->anticipo, 2) }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fs-4 fw-bold text-danger">
                                <span>Nuevo Saldo Pendiente:</span>
                                <span id="saldo-display">$0.00</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar Edición</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pasamos los datos de PHP a JavaScript de forma segura --}}
<script>
    const listaProductos = @json($productos);
    const detallesActuales = @json($pedido->detalles);
    const anticipoOriginal = {{ $pedido->anticipo }};
</script>

@endsection

@push('scripts')
<script>
    let contadorFilas = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Cargar los productos que ya tiene el pedido
        if (detallesActuales && detallesActuales.length > 0) {
            detallesActuales.forEach(detalle => {
                agregarFilaProducto(detalle);
            });
        } else {
            // Si por alguna razón no tiene productos, agregar una fila vacía
            agregarFilaProducto();
        }
        calcularTotales();
    });

    function agregarFilaProducto(datos = null) {
        const container = document.getElementById('productos-container');
        const index = contadorFilas++;
        
        // Construir opciones del select
        let opciones = '<option value="">Seleccione un producto...</option>';
        listaProductos.forEach(p => {
            const selected = (datos && datos.producto_id == p.id) ? 'selected' : '';
            opciones += `<option value="${p.id}" data-precio="${p.precio}" ${selected}>${p.nombre} - $${p.precio}</option>`;
        });

        // Valores por defecto o cargados
        const precioVal = datos ? datos.precio_unitario : 0;
        const cantVal = datos ? datos.cantidad : 1;
        const notasVal = datos ? (datos.especificaciones || '') : '';

        const html = `
            <div class="row mb-2 align-items-end fila-producto" id="fila-${index}">
                <div class="col-md-5">
                    <label class="small text-muted">Producto</label>
                    <select name="productos[${index}][id]" class="form-select select-producto" required onchange="actualizarPrecio(this, ${index})">
                        ${opciones}
                    </select>
                    {{-- Input oculto para guardar el precio unitario --}}
                    <input type="hidden" name="productos[${index}][precio]" class="input-precio-hidden" value="${precioVal}">
                </div>
                <div class="col-md-2">
                    <label class="small text-muted">Cant.</label>
                    <input type="number" name="productos[${index}][cantidad]" class="form-control input-cantidad" value="${cantVal}" min="1" required onchange="calcularTotales()">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Notas (Ej. Sin gluten)</label>
                    <input type="text" name="productos[${index}][notas]" class="form-control" value="${notasVal}" placeholder="Detalles...">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm mt-4" onclick="eliminarFila(${index})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
    }

    function actualizarPrecio(select, index) {
        const precio = select.options[select.selectedIndex].getAttribute('data-precio') || 0;
        const fila = document.getElementById(`fila-${index}`);
        fila.querySelector('.input-precio-hidden').value = precio;
        calcularTotales();
    }

    function eliminarFila(index) {
        // Evitar dejar el pedido sin productos
        const totalFilas = document.querySelectorAll('.fila-producto').length;
        if (totalFilas > 1) {
            document.getElementById(`fila-${index}`).remove();
            calcularTotales();
        } else {
            alert('El pedido debe tener al menos un producto.');
        }
    }

    function calcularTotales() {
        let total = 0;
        document.querySelectorAll('.fila-producto').forEach(fila => {
            const precio = parseFloat(fila.querySelector('.input-precio-hidden').value) || 0;
            const cantidad = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
            total += precio * cantidad;
        });

        const nuevoSaldo = total - anticipoOriginal;

        // Formateador de moneda
        const fmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
        
        document.getElementById('total-display').textContent = fmt.format(total);
        
        const saldoElement = document.getElementById('saldo-display');
        saldoElement.textContent = fmt.format(nuevoSaldo);
        
        // Lógica visual si el saldo es negativo (Devolución)
        if(nuevoSaldo < 0) {
            saldoElement.classList.remove('text-danger');
            saldoElement.classList.add('text-success');
            saldoElement.innerHTML = `${fmt.format(Math.abs(nuevoSaldo))} (Saldo a Favor)`;
        } else {
            saldoElement.classList.remove('text-success');
            saldoElement.classList.add('text-danger');
        }
    }
</script>
@endpush