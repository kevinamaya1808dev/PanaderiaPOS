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

                        {{-- Campos Manuales (se llenan solos si eliges cliente) --}}
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
                    <div class="card-footer bg-white">
                        <div class="row text-end align-items-center">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fs-5">Total:</span>
                                    <span class="fs-5 fw-bold" id="txtTotal">$0.00</span>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-success text-white">Anticipo (Paga hoy)</span>
                                    <input type="number" name="anticipo" id="inputAnticipo" class="form-control text-end fw-bold text-success" step="0.50" min="0" value="0" required>
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

{{-- MODAL PARA SELECCIONAR PRODUCTOS --}}
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

{{-- SCRIPTS PARA LA LÓGICA DEL FORMULARIO --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Llenar datos de cliente al seleccionar
        const selectCliente = document.getElementById('cliente_select');
        selectCliente.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if(this.value) {
                document.getElementById('nombre_cliente').value = selected.getAttribute('data-nombre');
                document.getElementById('telefono_cliente').value = selected.getAttribute('data-tel');
            } else {
                document.getElementById('nombre_cliente').value = '';
                document.getElementById('telefono_cliente').value = '';
            }
        });

        // 2. Lógica de Productos
        let totalGlobal = 0;
        let productoIndex = 0;
        const listaProductos = document.getElementById('listaProductos');
        const filaVacia = document.getElementById('filaVacia');
        const inputAnticipo = document.getElementById('inputAnticipo');
        
        // Al hacer clic en un producto del modal
        document.querySelectorAll('.btn-producto').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.nombre;
                const precio = parseFloat(this.dataset.precio);

                agregarFila(id, nombre, precio);
                
                // Cerrar modal (Bootstrap 5)
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalProductos'));
                modal.hide();
            });
        });

        function agregarFila(id, nombre, precio) {
            filaVacia.style.display = 'none';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="hidden" name="productos[${productoIndex}][id]" value="${id}">
                    <input type="hidden" name="productos[${productoIndex}][precio]" value="${precio}">
                    <strong>${nombre}</strong><br>
                    <input type="text" name="productos[${productoIndex}][notas]" class="form-control form-control-sm mt-1" placeholder="Nota del producto (ej. Relleno fresa)">
                </td>
                <td>
                    <input type="number" name="productos[${productoIndex}][cantidad]" class="form-control input-cantidad" value="1" min="1" onchange="actualizarFila(this, ${precio})">
                </td>
                <td class="align-middle">$${precio.toFixed(2)}</td>
                <td class="align-middle fw-bold text-success span-subtotal">$${precio.toFixed(2)}</td>
                <td class="align-middle text-end">
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            listaProductos.appendChild(row);
            productoIndex++;
            calcularTotales();
        }

        // Hacer funciones globales para que funcionen con onclick inline
        window.actualizarFila = function(input, precio) {
            const cantidad = parseInt(input.value) || 0;
            const subtotal = cantidad * precio;
            // Buscar el span de subtotal en la misma fila
            const row = input.closest('tr');
            row.querySelector('.span-subtotal').textContent = '$' + subtotal.toFixed(2);
            calcularTotales();
        };

        window.eliminarFila = function(btn) {
            btn.closest('tr').remove();
            if(listaProductos.children.length <= 1) { // Solo queda la fila vacía (oculta)
                filaVacia.style.display = 'table-row';
            }
            calcularTotales();
        };

        function calcularTotales() {
            let suma = 0;
            document.querySelectorAll('#listaProductos tr').forEach(row => {
                // Ignorar fila vacía
                if(row.id === 'filaVacia') return;

                const cantidadInput = row.querySelector('.input-cantidad');
                if(cantidadInput) {
                    const cantidad = parseInt(cantidadInput.value);
                    const precio = parseFloat(row.querySelector('input[type="hidden"][name*="[precio]"]').value);
                    suma += cantidad * precio;
                }
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

            if(saldo < 0) {
                // Alerta visual si el anticipo es mayor al total
                inputAnticipo.classList.add('is-invalid');
            } else {
                inputAnticipo.classList.remove('is-invalid');
            }
        }

        // Recalcular saldo al escribir anticipo
        inputAnticipo.addEventListener('input', calcularSaldo);
        
        // Filtro buscador modal
        document.getElementById('buscadorProducto').addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#listaOpcionesProductos button').forEach(btn => {
                const text = btn.textContent.toLowerCase();
                btn.style.display = text.includes(term) ? 'flex' : 'none';
            });
        });
    });
</script>
@endsection