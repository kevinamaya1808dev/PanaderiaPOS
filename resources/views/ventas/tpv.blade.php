@extends('layouts.app')

@section('content')
<div class="container-fluid" style="height: calc(100vh - 76px);"> {{-- Ocupa el espacio restante bajo el header --}}
    
    {{-- Solo muestra el TPV si la caja está abierta --}}
    @if ($cajaAbierta) 
        <div class="row h-100">
            <!-- 1. Columna de Productos y Categorías (8/12) -->
            <div class="col-lg-8 d-flex flex-column h-100">
                <h4 class="mb-3 text-primary"><i class="fas fa-bread-slice me-2"></i> Productos Disponibles</h4>
                
                <!-- Filtro de Categorías (Botones) -->
                <div class="d-flex mb-3 overflow-auto pb-2 border-bottom">
                    <button class="btn btn-sm btn-outline-dark me-2 active category-filter" data-category-id="all">Todas</button>
                    @foreach ($categorias as $cat)
                        <button class="btn btn-sm btn-outline-secondary me-2 category-filter" data-category-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
                    @endforeach
                </div>

                <!-- Lista de Productos (Tarjetas clickables) -->
                <div class="row g-3 flex-grow-1 overflow-auto p-2 border rounded shadow-sm bg-white" id="product-list">
                    @forelse ($productos as $producto)
                        <div class="col-4 col-sm-3 col-md-2 product-item" data-category-id="{{ $producto->categoria_id }}">
                            <div class="card h-100 product-card shadow-sm border-0" 
                                 style="cursor: pointer;"
                                 data-id="{{ $producto->id }}" 
                                 data-name="{{ $producto->nombre }}"
                                 data-price="{{ $producto->precio }}"
                                 data-stock="{{ $producto->inventario->stock ?? 0 }}">
                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                    <h6 class="card-title mb-1 fw-bold fs-sm">{{ $producto->nombre }}</h6>
                                    <div>
                                        <p class="card-text text-success fs-5 mb-0">${{ number_format($producto->precio, 2) }}</p>
                                        <small class="text-muted product-stock">Stock: {{ $producto->inventario->stock ?? 0 }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning w-100 text-center">No hay productos con stock disponible para la venta.</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Columna del Carrito y Pago (4/12) -->
            <div class="col-lg-4 d-flex flex-column border-start ps-4 h-100">
                <h4 class="mb-3 text-danger"><i class="fas fa-shopping-cart me-2"></i> Orden Actual</h4>
                
                <!-- Cliente y Vendedor -->
                <div class="alert alert-info py-2 mb-3">
                    <div><small>Cajero: <strong>{{ Auth::user()->name }}</strong></small></div>
                    <hr class="my-1">
                    <div><small>Cliente: <strong id="selected-client-name">Público General</strong> 
                        <a href="#" class="btn btn-sm btn-outline-secondary py-0 ms-2" id="select-client-btn" data-bs-toggle="modal" data-bs-target="#clienteModal">
                            <i class="fas fa-user-edit"></i> Seleccionar/Añadir
                        </a>
                    </small></div>
                </div>
                
                <!-- Carrito de Compras -->
                <div id="cart" class="flex-grow-1 overflow-auto border rounded p-3 mb-3 bg-light shadow-sm">
                    <p class="text-center text-muted empty-cart-message">Añada productos para comenzar la venta.</p>
                </div>

                <!-- Resumen y Botones de Pago -->
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between fw-bold mb-1">
                        <span>SUBTOTAL:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    {{-- Aquí irían impuestos si los hubiera --}}
                    <div class="d-flex justify-content-between fw-bold fs-5 text-danger mb-3">
                        <span>TOTAL A PAGAR:</span>
                        <span id="total">$0.00</span>
                    </div>

                    <button id="process-payment" class="btn btn-success btn-lg w-100 mb-2 disabled">
                        <i class="fas fa-money-check-alt me-2"></i> Procesar Venta
                    </button>
                    <button id="cancel-order" class="btn btn-outline-danger w-100">
                        <i class="fas fa-times-circle me-2"></i> Cancelar Orden
                    </button>
                </div>
            </div>
        </div>

    {{-- SI LA CAJA ESTÁ CERRADA --}}
    @else
        <div class="alert alert-danger text-center mx-auto mt-5 shadow" style="max-width: 600px;">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> ¡Caja Cerrada!</h4>
            <p>No se puede realizar ninguna venta hasta que abras la caja.</p>
            <hr>
            <a href="{{ route('cajas.index') }}" class="btn btn-danger">
                <i class="fas fa-box-open me-2"></i> Ir a Gestión de Caja para Abrir
            </a>
        </div>
    @endif
</div>

{{-- MODAL PARA SELECCIONAR/CREAR CLIENTE --}}
<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="clienteModalLabel">Seleccionar o Crear Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- Aquí iría un buscador y el formulario para crear cliente rápido vía AJAX --}}
        <p>Buscador y formulario de creación rápida de cliente (pendiente).</p>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Usar Público General</button>
      </div>
    </div>
  </div>
</div>


<script>
    // **********************************************
    // LÓGICA DE JAVASCRIPT DEL PUNTO DE VENTA (TPV)
    // **********************************************
    
    // Variables de Estado
    const cart = {}; // Almacena {producto_id: {name, price, qty, stock}}
    let selectedClientId = null; // ID del cliente seleccionado
    let selectedClientName = 'Público General';
    
    // Referencias del DOM
    const cartDiv = document.getElementById('cart');
    const subtotalSpan = document.getElementById('subtotal');
    const totalSpan = document.getElementById('total');
    const processButton = document.getElementById('process-payment');
    const cancelButton = document.getElementById('cancel-order');
    const clientNameSpan = document.getElementById('selected-client-name');
    const productListDiv = document.getElementById('product-list');
    const categoryFilters = document.querySelectorAll('.category-filter');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const emptyCartMessage = '<p class="text-center text-muted empty-cart-message">Añada productos para comenzar la venta.</p>';

    // Función para actualizar la interfaz del carrito
    function updateCartUI() {
        let subtotal = 0;
        let itemCount = 0;
        cartDiv.innerHTML = ''; // Limpiar carrito
        
        for (const id in cart) {
            const item = cart[id];
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;
            itemCount++;

            const itemElement = document.createElement('div');
            itemElement.className = 'd-flex justify-content-between border-bottom py-2 align-items-center cart-item';
            itemElement.innerHTML = `
                <div class="flex-grow-1 me-2">
                    <span class="badge bg-dark me-2">${item.qty}x</span>
                    <span class="fw-bold">${item.name}</span>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-outline-danger me-2 remove-item" data-id="${id}" title="Restar">-</button>
                    <span class="fw-bold me-2" style="min-width: 60px; text-align: right;">$${itemTotal.toFixed(2)}</span>
                    <button class="btn btn-sm btn-outline-success add-item" data-id="${id}" title="Añadir">+</button>
                </div>
            `;
            cartDiv.appendChild(itemElement);
        }

        if (itemCount === 0) {
            cartDiv.innerHTML = emptyCartMessage;
            processButton.classList.add('disabled');
        } else {
            processButton.classList.remove('disabled');
        }

        // Actualizar totales
        subtotalSpan.textContent = `$${subtotal.toFixed(2)}`;
        totalSpan.textContent = `$${subtotal.toFixed(2)}`;
    }

    // Lógica para añadir ítem al carrito
    function addItem(id, name, price, stock) {
        if (!stock || stock <= 0) {
            alert(`El producto ${name} está agotado.`);
            return;
        }
        
        if (cart[id]) {
            // No permitir añadir más si se alcanza el stock
            if (cart[id].qty < stock) { 
                cart[id].qty++;
            } else {
                alert(`No puedes añadir más de ${stock} unidades de ${name} (stock disponible).`);
            }
        } else {
            cart[id] = { name, price, qty: 1, stock };
        }
        updateCartUI();
    }
    
    // Lógica para remover ítem del carrito
    function removeItem(id) {
        if (cart[id]) {
            cart[id].qty--;
            if (cart[id].qty <= 0) {
                delete cart[id]; // Eliminar el producto si la cantidad es 0 o menos
            }
        }
        updateCartUI();
    }

    // Manejador de eventos en el carrito (delegación)
    cartDiv.addEventListener('click', function(e) {
        const target = e.target;
        if (target.classList.contains('add-item')) {
            const id = target.dataset.id;
            const item = cart[id];
            if (item) addItem(id, item.name, item.price, item.stock);
        } else if (target.classList.contains('remove-item')) {
            const id = target.dataset.id;
            removeItem(id);
        }
    });

    // Evento para añadir productos al carrito (desde las tarjetas)
    productListDiv.addEventListener('click', function(e) {
        const card = e.target.closest('.product-card');
        if (card) {
            const id = card.dataset.id;
            const price = parseFloat(card.dataset.price);
            const name = card.dataset.name;
            const stock = parseInt(card.dataset.stock);
            
            addItem(id, name, price, stock);
        }
    });

    // Evento para cancelar la orden
    cancelButton.addEventListener('click', function() {
        if (Object.keys(cart).length > 0 && confirm('¿Está seguro de cancelar la orden actual y vaciar el carrito?')) {
            for (const id in cart) {
                delete cart[id];
            }
            selectedClientId = null;
            selectedClientName = 'Público General';
            clientNameSpan.textContent = selectedClientName;
            updateCartUI();
        }
    });
    
    // Evento para procesar el pago (Envío a Laravel)
    processButton.addEventListener('click', async function() {
        if (Object.keys(cart).length === 0 || this.classList.contains('disabled')) return;

        // Crear array de detalles de venta
        const detalles = Object.keys(cart).map(id => ({
            producto_id: id,
            cantidad: cart[id].qty,
            precio_unitario: cart[id].price,
            importe: cart[id].price * cart[id].qty
        }));
        
        const total = parseFloat(totalSpan.textContent.replace('$', ''));
        
        // **Falta Lógica de Modal de Pago**
        // Aquí deberías abrir un modal para seleccionar método de pago,
        // ingresar monto recibido y calcular cambio.
        // Por ahora, simulamos pago exacto en efectivo.
        const metodoPagoSimulado = 'efectivo';
        const montoRecibidoSimulado = total;
        const montoEntregadoSimulado = 0;

        // Datos a enviar al controlador
        const payload = {
            _token: csrfToken,
            cliente_id: selectedClientId, 
            metodo_pago: metodoPagoSimulado,
            total: total,
            monto_recibido: montoRecibidoSimulado,
            monto_entregado: montoEntregadoSimulado,
            detalles: detalles
        };

        // Deshabilitar botón mientras procesa
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

        try {
            const response = await fetch("{{ route('ventas.store') }}", { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok) {
                alert('Venta procesada exitosamente! ID: ' + result.venta_id);
                // Vaciar carrito y recargar página para reflejar stock
                for (const id in cart) { delete cart[id]; }
                updateCartUI();
                window.location.reload(); 
            } else {
                // Mostrar errores de validación (ej. stock insuficiente) o errores generales
                let errorMessage = result.message || 'Error desconocido.';
                if (result.errors) {
                    errorMessage += '\nDetalles:\n';
                    for (const field in result.errors) {
                        errorMessage += `- ${result.errors[field].join(', ')}\n`;
                    }
                }
                alert('Error al procesar la venta: \n' + errorMessage);
            }
        } catch (e) {
            console.error('Error de conexión:', e);
            alert('Error de conexión o del servidor al procesar la venta.');
        } finally {
            // Reactivar botón
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-money-check-alt me-2"></i> Procesar Venta';
        }
    });

    // Lógica para Filtro de Categorías
    categoryFilters.forEach(button => {
        button.addEventListener('click', function() {
            // Quitar 'active' de todos los botones
            categoryFilters.forEach(btn => btn.classList.remove('active', 'btn-outline-dark'));
            categoryFilters.forEach(btn => btn.classList.add('btn-outline-secondary'));
            // Añadir 'active' al botón clickeado
            this.classList.add('active', 'btn-outline-dark');
            this.classList.remove('btn-outline-secondary');

            const categoryId = this.dataset.categoryId;
            const productItems = productListDiv.querySelectorAll('.product-item');

            productItems.forEach(item => {
                if (categoryId === 'all' || item.dataset.categoryId === categoryId) {
                    item.style.display = 'block'; // Mostrar
                } else {
                    item.style.display = 'none'; // Ocultar
                }
            });
        });
    });

    // Lógica básica para el modal de clientes (pendiente AJAX)
    document.getElementById('select-client-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Aquí abrirías el modal y manejarías la selección/creación
        // Por ahora, solo simula la selección
        // let newClientId = prompt("Ingresa el ID del cliente (dejar vacío para Público General):");
        // selectedClientId = newClientId ? parseInt(newClientId) : null;
        // selectedClientName = newClientId ? `Cliente ID ${newClientId}` : 'Público General'; // Se necesitaría AJAX para obtener el nombre
        // clientNameSpan.textContent = selectedClientName;
        alert('Modal de selección/creación de cliente pendiente de implementar con AJAX.');
    });

    // Inicializar la interfaz al cargar
    document.addEventListener('DOMContentLoaded', updateCartUI);
</script>

<style>
/* Estilos adicionales para mejorar la interfaz TPV */
.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,.12)!important;
    transition: all 0.2s ease-in-out;
}
.fs-sm { font-size: 0.8rem; } /* Tamaño de fuente más pequeño */
</style>
@endsection