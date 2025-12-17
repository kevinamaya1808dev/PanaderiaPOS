@extends('layouts.app')

@section('content')
<div class="container">
    <form action="{{ route('pedidos.store') }}" method="POST" id="formPedido">
        @csrf

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Nuevo Pedido Especial</h2>
            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>

        <div class="row">
            {{-- COLUMNA IZQUIERDA: Datos del Cliente y Entrega --}}
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-user me-2"></i> Datos del Cliente
                    </div>
                    <div class="card-body">

                        {{-- Campos Manuales --}}
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control" required placeholder="Ej: Sra. Mari">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono de contacto</label>
                            <input type="text" name="telefono_cliente" id="telefono_cliente" class="form-control" placeholder="Para avisar cuando esté listo">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Fecha y Hora de Entrega *</label>
                            <input type="datetime-local" name="fecha_entrega" class="form-control" required min="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notas Generales / Dedicatoria</label>
                            <textarea name="notas_especiales" class="form-control" rows="3" placeholder="Ej: Escribir 'Felicidades' en azul..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Productos y Pagos --}}
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-bread-slice me-2"></i> Productos</span>
                        <button type="button" class="btn btn-sm btn-light text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalProductos">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0" id="tablaDetalles">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th width="100">Cant.</th>
                                        <th width="120">Precio</th>
                                        <th width="120">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="listaProductos">
                                    {{-- Aquí se agregarán los productos con JS --}}
                                    <tr id="filaVacia">
                                        <td colspan="5" class="text-center text-muted p-4">
                                            Agrega productos al pedido...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    {{-- FOOTER CON TOTALES Y PAGOS --}}
                    <div class="card-footer bg-white">
                        <div class="row text-end align-items-center">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fs-5">Total:</span>
                                    <span class="fs-5 fw-bold" id="txtTotal">$0.00</span>
                                </div>
                                
                                <hr>

                                {{-- Selector de Método de Pago --}}
                                <div class="mb-2 text-start">
                                    <label class="form-label small fw-bold">Método de Pago (Anticipo):</label>
                                    <select name="metodo_pago" id="selectMetodoPago" class="form-select form-select-sm">
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Tarjeta">Tarjeta</option>
                                    </select>
                                </div>

                                {{-- Input Referencia (Oculto por defecto) --}}
                                <div class="mb-2 text-start" id="divReferenciaPago" style="display: none;">
                                    <label class="form-label small fw-bold">Referencia / Folio:</label>
                                    <input type="text" name="referencia_pago" id="inputReferenciaPago" class="form-control form-control-sm" placeholder="4 últimos dígitos o n° autorización">
                                </div>

                                {{-- Input Anticipo CON BOTÓN CALCULADORA --}}
                                <label class="form-label small fw-bold text-start w-100 mb-1">Anticipo (Paga hoy)</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-success text-white fw-bold">$</span>
                                    
                                    <input type="number" name="anticipo" id="inputAnticipo" class="form-control text-end fw-bold text-success" step="0.50" min="0" value="0" required>
                                    
                                    {{-- NUEVO: Botón Calculadora (Solo aparece en efectivo) --}}
                                    <button class="btn btn-outline-secondary" type="button" id="btnCalculadora" title="Calcular Cambio">
                                        <i class="fas fa-calculator"></i>
                                    </button>
                                </div>

                                <div class="d-flex justify-content-between text-danger fw-bold">
                                    <span>Resta por pagar:</span>
                                    <span id="txtSaldo">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg shadow">
                        <i class="fas fa-save me-2"></i> Guardar Pedido
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- MODAL 1: SELECCIONAR PRODUCTOS --}}
<div class="modal fade" id="modalProductos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscadorProducto" class="form-control mb-3" placeholder="Buscar producto...">
                <div class="list-group" id="listaOpcionesProductos" style="max-height: 300px; overflow-y: auto;">
                    @foreach($productos as $prod)
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-producto" 
                            data-id="{{ $prod->id }}" 
                            data-nombre="{{ $prod->nombre }}" 
                            data-precio="{{ $prod->precio }}">
                            <span>{{ $prod->nombre }}</span>
                            <span class="badge bg-primary rounded-pill">${{ number_format($prod->precio, 2) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2 (NUEVO): CALCULADORA DE CAMBIO --}}
<div class="modal fade" id="modalCalculadora" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered"> <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fas fa-cash-register me-2"></i>Calcular Cambio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Monto del Anticipo (Solo lectura) --}}
                <div class="mb-3 text-center">
                    <label class="small text-muted fw-bold">El cliente debe dejar (Anticipo):</label>
                    <h3 class="text-success fw-bold" id="calcMontoAnticipo">$0.00</h3>
                </div>

                {{-- Input Paga Con --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Paga con:</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="calcPagaCon" class="form-control form-control-lg fw-bold" placeholder="0.00">
                    </div>
                </div>

                {{-- Resultado Cambio --}}
                <div class="alert alert-light border text-center">
                    <label class="small text-muted fw-bold">Su Cambio:</label>
                    <h2 class="text-primary fw-bold mb-0" id="calcResultado">$0.00</h2>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- VARIABLES GLOBALES ---
        let totalGlobal = 0;
        let productoIndex = 0;
        const listaProductos = document.getElementById('listaProductos');
        const filaVacia = document.getElementById('filaVacia');
        const inputAnticipo = document.getElementById('inputAnticipo');
        const selectMetodo = document.getElementById('selectMetodoPago');
        const divReferencia = document.getElementById('divReferenciaPago');
        const inputReferencia = document.getElementById('inputReferenciaPago');
        const btnCalculadora = document.getElementById('btnCalculadora');

        // --- 1. Lógica de Productos (Sin cambios mayores) ---
        document.querySelectorAll('.btn-producto').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;
                const precio = parseFloat(this.dataset.precio);
                agregarFila(id, nombre, precio);
                const modalEl = document.getElementById('modalProductos');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            });
        });

        function agregarFila(id, nombre, precio) {
            if(filaVacia) filaVacia.style.display = 'none';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="hidden" name="productos[${productoIndex}][id]" value="${id}">
                    <input type="hidden" name="productos[${productoIndex}][precio]" value="${precio}">
                    <strong>${nombre}</strong><br>
                    <input type="text" name="productos[${productoIndex}][notas]" class="form-control form-control-sm mt-1" placeholder="Nota del producto">
                </td>
                <td>
                    <input type="number" name="productos[${productoIndex}][cantidad]" class="form-control input-cantidad text-center" value="1" min="1" onchange="window.actualizarFila(this, ${precio})">
                </td>
                <td class="align-middle">$${precio.toFixed(2)}</td>
                <td class="align-middle fw-bold text-success span-subtotal">$${precio.toFixed(2)}</td>
                <td class="align-middle text-end">
                    <button type="button" class="btn btn-sm btn-danger" onclick="window.eliminarFila(this)"><i class="fas fa-trash"></i></button>
                </td>
            `;
            listaProductos.appendChild(row);
            productoIndex++;
            calcularTotales();
        }

        window.actualizarFila = function(input, precio) {
            const cantidad = parseInt(input.value) || 0;
            const subtotal = cantidad * precio;
            const row = input.closest('tr');
            row.querySelector('.span-subtotal').textContent = '$' + subtotal.toFixed(2);
            calcularTotales();
        };

        window.eliminarFila = function(btn) {
            btn.closest('tr').remove();
            if(listaProductos.querySelectorAll('tr').length <= 1) { 
                if(filaVacia) filaVacia.style.display = 'table-row';
            }
            calcularTotales();
        };

        function calcularTotales() {
            let suma = 0;
            document.querySelectorAll('.input-cantidad').forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const row = input.closest('tr');
                const precioInput = row.querySelector('input[name*="[precio]"]');
                const precio = parseFloat(precioInput.value) || 0;
                suma += cantidad * precio;
            });
            totalGlobal = suma;
            document.getElementById('txtTotal').textContent = '$' + totalGlobal.toFixed(2);
            calcularSaldo();
        }

        function calcularSaldo() {
            const anticipo = parseFloat(inputAnticipo.value) || 0;
            const saldo = totalGlobal - anticipo;
            const spanSaldo = document.getElementById('txtSaldo');
            spanSaldo.textContent = '$' + (saldo < 0 ? '0.00' : saldo.toFixed(2));
            if(saldo < 0) { inputAnticipo.classList.add('is-invalid'); } 
            else { inputAnticipo.classList.remove('is-invalid'); }
        }

        if(inputAnticipo) {
            inputAnticipo.addEventListener('input', calcularSaldo);
        }
        
        // --- 2. Lógica Método de Pago y Botón Calculadora ---
        if(selectMetodo) {
            selectMetodo.addEventListener('change', function() {
                const metodo = this.value;
                if (metodo === 'Tarjeta' || metodo === 'Transferencia') {
                    divReferencia.style.display = 'block';
                    inputReferencia.required = true;
                    btnCalculadora.style.display = 'none'; // Ocultar calculadora
                } else {
                    divReferencia.style.display = 'none';
                    inputReferencia.value = ''; 
                    inputReferencia.required = false;
                    btnCalculadora.style.display = 'block'; // Mostrar calculadora
                }
            });
        }

        // --- 3. Lógica del MODAL CALCULADORA ---
        const modalCalculadora = new bootstrap.Modal(document.getElementById('modalCalculadora'));
        const calcMontoAnticipo = document.getElementById('calcMontoAnticipo');
        const calcPagaCon = document.getElementById('calcPagaCon');
        const calcResultado = document.getElementById('calcResultado');

        // Abrir modal al hacer click en el botón calculadora
        if(btnCalculadora) {
            btnCalculadora.addEventListener('click', function() {
                const anticipoVal = parseFloat(inputAnticipo.value) || 0;
                
                if(anticipoVal <= 0) {
                    alert('Primero ingresa el monto del anticipo para calcular el cambio.');
                    inputAnticipo.focus();
                    return;
                }

                // Preparar modal
                const formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
                calcMontoAnticipo.textContent = formatter.format(anticipoVal);
                calcPagaCon.value = '';
                calcResultado.textContent = '$0.00';
                
                modalCalculadora.show();

                // Poner el foco en el input después de que abra el modal
                setTimeout(() => { calcPagaCon.focus(); }, 500);
            });
        }

        // Calcular cambio en tiempo real dentro del modal
        if(calcPagaCon) {
            calcPagaCon.addEventListener('input', function() {
                const anticipoVal = parseFloat(inputAnticipo.value) || 0;
                const pagaConVal = parseFloat(this.value) || 0;
                const cambio = pagaConVal - anticipoVal;

                const formatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

                if(cambio < 0) {
                    calcResultado.textContent = "Falta dinero";
                    calcResultado.classList.add('text-danger');
                    calcResultado.classList.remove('text-primary');
                } else {
                    calcResultado.textContent = formatter.format(cambio);
                    calcResultado.classList.remove('text-danger');
                    calcResultado.classList.add('text-primary');
                }
            });
        }

        // Filtro buscador productos
        const buscador = document.getElementById('buscadorProducto');
        if(buscador) {
            buscador.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                document.querySelectorAll('#listaOpcionesProductos button').forEach(btn => {
                    const text = btn.textContent.toLowerCase(); 
                    if (text.includes(term)) {
                        btn.style.setProperty('display', 'flex', 'important');
                    } else {
                        btn.style.setProperty('display', 'none', 'important');
                    }
                });
            });
        }
    });
</script>
@endsection