@extends('layouts.app')

@section('content')
<div class="container-fluid" style="height: calc(100vh - 76px);"> 
    
    @if ($cajaAbierta) 
        <div class="row h-100">
            <!-- 1. Columna de Productos -->
            <div class="col-lg-8 d-flex flex-column h-100">
                <h4 class="mb-3 text-primary"><i class="fas fa-bread-slice me-2"></i> Productos Disponibles</h4>
                <div class="input-group mb-3 shadow-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="search" id="product-search" class="form-control" placeholder="Buscar producto por nombre...">
                </div>
                <div class="d-flex mb-3 overflow-auto pb-2 border-bottom">
                    <button class="btn btn-sm btn-outline-dark me-2 active category-filter" data-category-id="all">Todas</button>
                    @foreach ($categorias as $cat)
                        <button class="btn btn-sm btn-outline-secondary me-2 category-filter" data-category-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
                    @endforeach
                </div>
                
                {{-- Contenedor de productos --}}
                <div class="row g-3 overflow-auto p-2 border rounded shadow-sm bg-white" id="product-list" style="max-height: calc(100vh - 220px);">
                    @forelse ($productos as $producto)
                        <div class="col-4 col-sm-3 col-md-2 product-item" data-category-id="{{ $producto->categoria_id }}">
                            
                            <div class="card h-100 product-card shadow-sm border-0" 
                                 style="cursor: pointer;"
                                 data-id="{{ $producto->id }}" 
                                 data-name="{{ $producto->nombre }}" 
                                 data-price="{{ $producto->precio }}"
                                 data-stock="{{ $producto->inventario->stock ?? 0 }}"
                                 data-image="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : 'https://placehold.co/100x100/EBF5FB/333333?text=Sin+Imagen' }}">
                                
                                <div style="height: 100px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                    <img src="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : 'https://placehold.co/100x100/EBF5FB/333333?text=Sin+Imagen' }}" 
                                         class="card-img-top" 
                                         alt="{{ $producto->nombre }}"
                                         style="max-height: 100%; width: auto; object-fit: cover;">
                                </div>

                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                    <h6 class="card-title mb-1 fw-bold fs-sm product-name">{{ $producto->nombre }}</h6> 
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

            <!-- 2. Columna del Carrito y Pago -->
            <div class="col-lg-4 d-flex flex-column border-start ps-4 h-100">
                <h4 class="mb-3 text-danger"><i class="fas fa-shopping-cart me-2"></i> Orden Actual</h4>
                
                {{-- Cliente Editable con Dropdown --}}
                <div class="alert alert-info py-2 mb-3">
                    <div><small>Cajero: <strong>{{ Auth::user()->name }}</strong></small></div>
                    <hr class="my-1">
                    <div class="d-flex align-items-center">
                        <small class="me-2">Cliente:</small> 
                        <div class="input-group input-group-sm flex-grow-1">
                            <input type="text" id="temporal-client-name" class="form-control" placeholder="Público General">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Seleccionar Cliente Existente"></button>
                            <ul class="dropdown-menu dropdown-menu-end" id="client-dropdown-menu" style="max-height: 200px; overflow-y: auto;">
                                <li><a class="dropdown-item select-client" href="#" data-client-id="" data-client-name="Público General">Público General</a></li>
                                <li><hr class="dropdown-divider"></li>
                            </ul>
                        </div>
                    </div>
               </div>
                
                {{-- Carrito y Pago --}}
                <div id="cart" class="flex-grow-1 overflow-auto border rounded p-3 mb-3 bg-light shadow-sm">
                       <p class="text-center text-muted empty-cart-message">Añada productos para comenzar la venta.</p>
                </div>
                <div class="border-top pt-3">
                    {{-- Resumen Total --}}
                    <div class="d-flex justify-content-between fw-bold mb-1">
                        <span>SUBTOTAL:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 text-danger mb-3">
                        <span>TOTAL A PAGAR:</span>
                        <span id="total">$0.00</span>
                    </div>
                    {{-- Botones --}}
                    <button id="process-payment" class="btn btn-success btn-lg w-100 mb-2 disabled" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-dollar-sign me-2"></i> Cobrar Ahora
                    </button>

                    <button id="btn-generar-ticket" class="btn btn-primary btn-lg w-100 mb-2 disabled"> 
                        <i class="fas fa-ticket-alt me-2"></i> Generar Ticket (Pagar en Caja)
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

{{-- MODAL PARA SELECCIONAR CLIENTE --}}
<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="clienteModalLabel">Seleccionar o Crear Cliente</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <p>Buscador y formulario de creación rápida de cliente (pendiente).</p>
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Usar Público General</button>
       </div>
     </div>
   </div>
</div>

{{-- MODAL DE PAGO --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-cash-register me-2"></i> Procesar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <label class="form-label fs-5">Total a Pagar:</label>
                    <div class="fs-1 fw-bolder text-danger" id="modal-total-display">$0.00</div>
                </div>
                
                {{-- Método de Pago --}}
                <div class="mb-3">
                    <label for="modal-metodo-pago" class="form-label fw-bold">Método de Pago</label>
                    <select class="form-select form-select-lg" id="modal-metodo-pago">
                        <option value="efectivo" selected>Efectivo</option>
                        <option value="tarjeta">Tarjeta</option> 
                    </select>
                </div>
                
                {{-- Grupo para Efectivo --}}
                <div id="efectivo-fields"> 
                    <div class="mb-3" id="monto-recibido-group">
                        <label for="modal-monto-recibido" class="form-label fw-bold">Monto Recibido</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" id="modal-monto-recibido" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3 text-center" id="cambio-group">
                        <label class="form-label fs-5">Cambio a Entregar:</label>
                        <div class="fs-2 fw-bold text-info" id="modal-cambio-display">$0.00</div>
                    </div>
                </div>

                {{-- Grupo para Tarjeta (Folio) --}}
                <div class="mb-3" id="tarjeta-fields" style="display: none;"> 
                    <label for="modal-folio-pago" class="form-label fw-bold">Folio / Autorización</label>
                    <input type="text" class="form-control form-control-lg" id="modal-folio-pago" placeholder="Ingrese N° de autorización">
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-lg" id="confirm-payment-btn" disabled> 
                    <i class="fas fa-check-circle me-2"></i> Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>
{{-- ***** FIN MODAL DE PAGO ***** --}}


{{-- MODAL DE CONFIRMACIÓN (El que ya tenías) --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="confirmationModalHeader">
                <h5 class="modal-title" id="confirmationModalTitle">Confirmación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                ¿Estás seguro?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="confirmationModalConfirmButton">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================== --}}
{{-- CAMBIO 1: AÑADIDO EL HTML DEL MODAL DE ALERTA --}}
{{-- ========================================================== --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="alertModalHeader">
                <h5 class="modal-title" id="alertModalTitle">Alerta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="alertModalBody">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


{{-- ***** IFRAME OCULTO PARA IMPRESIÓN ***** --}}
<iframe id="print-frame" name="printFrame" 
    style="position: absolute; top: -9999px; left: -9999px; width: 1px; height: 1px; visibility: hidden; border: 0;">
</iframe>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables de Estado
        const cart = {}; 
        let selectedClientId = null; 
        let currentCategoryId = 'all'; 
        const clients = @json($clientes ?? []); 
        
        // Referencias del DOM
        const cartDiv = document.getElementById('cart');
        const subtotalSpan = document.getElementById('subtotal');
        const totalSpan = document.getElementById('total');
        const processButton = document.getElementById('process-payment'); 
        const cancelButton = document.getElementById('cancel-order');
        const temporalClientInput = document.getElementById('temporal-client-name'); 
        const clientDropdownMenu = document.getElementById('client-dropdown-menu'); 
        const productListDiv = document.getElementById('product-list');
        const categoryFilters = document.querySelectorAll('.category-filter');
        const searchInput = document.getElementById('product-search'); 
        const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
        const emptyCartMessageHTML = '<p class="text-center text-muted empty-cart-message">Añada productos para comenzar la venta.</p>';
        const paymentModalElement = document.getElementById('paymentModal');
        let paymentModal = null;
        if (paymentModalElement && typeof bootstrap !== 'undefined') { 
             paymentModal = new bootstrap.Modal(paymentModalElement); 
        }
        const modalTotalDisplay = document.getElementById('modal-total-display');
        const modalMetodoPago = document.getElementById('modal-metodo-pago');
        const modalMontoRecibido = document.getElementById('modal-monto-recibido');
        const modalCambioDisplay = document.getElementById('modal-cambio-display');
        const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
        const efectivoFields = document.getElementById('efectivo-fields'); 
        const tarjetaFields = document.getElementById('tarjeta-fields'); 
        const modalFolioPago = document.getElementById('modal-folio-pago'); 
        const printFrame = document.getElementById('print-frame');
        const btnGenerarTicket = document.getElementById('btn-generar-ticket'); 
        
        // ==========================================================
        // CAMBIO 2: NUEVAS REFERENCIAS Y FUNCIONES PARA LOS MODALS
        // ==========================================================
        const confirmationModalElement = document.getElementById('confirmationModal');
        let confirmationModal = null;
        if (confirmationModalElement && typeof bootstrap !== 'undefined') {
            confirmationModal = new bootstrap.Modal(confirmationModalElement);
        }
        const confirmationModalTitle = document.getElementById('confirmationModalTitle');
        const confirmationModalBody = document.getElementById('confirmationModalBody');
        const confirmationModalConfirmButton = document.getElementById('confirmationModalConfirmButton');
        const confirmationModalHeader = document.getElementById('confirmationModalHeader');

        const alertModalElement = document.getElementById('alertModal');
        let alertModal = null;
        if (alertModalElement && typeof bootstrap !== 'undefined') {
            alertModal = new bootstrap.Modal(alertModalElement);
        }
        const alertModalTitle = document.getElementById('alertModalTitle');
        const alertModalBody = document.getElementById('alertModalBody');
        const alertModalHeader = document.getElementById('alertModalHeader');

        /**
         * Muestra un modal de ALERTA (reemplaza alert())
         * @param {string} body - El mensaje de alerta.
         * @param {string} title - El título (ej. 'Stock Insuficiente').
         * @param {string} type - 'danger' (rojo) o 'warning' (amarillo).
         */
        function showAlertModal(body, title = 'Atención', type = 'danger') {
            if (!alertModal) {
                alert(body); // Fallback si el modal no cargó
                return;
            }
            alertModalTitle.textContent = title;
            alertModalBody.textContent = body;
            
            alertModalHeader.className = 'modal-header text-white'; // Resetea clases
            alertModalHeader.classList.add(type === 'danger' ? 'bg-danger' : 'bg-warning');
            
            alertModal.show();
        }

        /**
         * Muestra un modal de CONFIRMACIÓN (reemplaza confirm())
         */
        function showConfirmationModal(title, body, confirmText, confirmClass, callback) {
            if (!confirmationModal) return;

            confirmationModalTitle.textContent = title;
            confirmationModalBody.textContent = body;
            confirmationModalConfirmButton.textContent = confirmText;

            confirmationModalConfirmButton.className = 'btn'; 
            confirmationModalHeader.className = 'modal-header text-white';
            confirmationModalConfirmButton.classList.add(confirmClass);
            
            let headerClass = confirmClass.replace('btn-', 'bg-');
            if(headerClass.includes('outline')){
                headerClass = 'bg-secondary';
            }
            confirmationModalHeader.classList.add(headerClass);

            // Re-crear el botón para evitar listeners duplicados
            const newConfirmButton = confirmationModalConfirmButton.cloneNode(true);
            confirmationModalConfirmButton.parentNode.replaceChild(newConfirmButton, confirmationModalConfirmButton);
            
            const newButtonReference = document.getElementById('confirmationModalConfirmButton');
            
            newButtonReference.addEventListener('click', function() {
                callback(); 
                confirmationModal.hide(); 
            });

            confirmationModal.show();
        }

        // ==========================================================
        // LÓGICA DE ACTUALIZACIÓN DEL CARRITO
        // ==========================================================
        function setCartQuantity(id, newQty) {
            if (!cart[id]) return; 
            const item = cart[id];
            const stock = item.stock;
            newQty = parseInt(newQty, 10);
            if (isNaN(newQty) || newQty < 0) {
                newQty = 1; 
            }
            if (newQty > stock) {
                // CAMBIO 3: Reemplazado alert()
                showAlertModal(`Stock insuficiente. Solo quedan ${stock} unidades de ${item.name}.`, 'Stock Insuficiente', 'warning');
                newQty = stock; 
            }
            if (newQty <= 0) {
                delete cart[id];
            } else {
                item.qty = newQty;
            }
            updateCartUI();
        }
        
        function updateCartUI() {
            // ... (Lógica de UI sin cambios) ...
            let subtotal = 0;
            let itemCount = 0;
            if (!cartDiv) { console.error("Elemento cartDiv no encontrado."); return; } 
            cartDiv.innerHTML = ''; 
            for (const id in cart) { 
                const item = cart[id];
                const itemTotal = item.price * item.qty;
                subtotal += itemTotal;
                itemCount++;
                const itemElement = document.createElement('div');
                itemElement.className = 'd-flex justify-content-between border-bottom py-2 align-items-center cart-item';
                itemElement.innerHTML = `
                    <div class="flex-grow-1 me-2 d-flex align-items-center">
                        <input type="number" class="form-control form-control-sm cart-item-qty" value="${item.qty}" data-id="${id}" min="0" max="${item.stock}" style="width: 60px; text-align: center; margin-right: 10px;">
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
                if (!cartDiv.querySelector('.cart-item')) { cartDiv.innerHTML = emptyCartMessageHTML; }
                if (processButton) { processButton.classList.add('disabled'); processButton.disabled = true; }
                if (btnGenerarTicket) { btnGenerarTicket.classList.add('disabled'); btnGenerarTicket.disabled = true; }
            } else { 
                const emptyMsg = cartDiv.querySelector('.empty-cart-message');
                if(emptyMsg) emptyMsg.remove();
                if (processButton) { processButton.classList.remove('disabled'); processButton.disabled = false; }
                if (btnGenerarTicket) { btnGenerarTicket.classList.remove('disabled'); btnGenerarTicket.disabled = false; }
            }
            if (subtotalSpan) subtotalSpan.textContent = `$${subtotal.toFixed(2)}`;
            if (totalSpan) totalSpan.textContent = `$${subtotal.toFixed(2)}`;
            if (modalTotalDisplay) modalTotalDisplay.textContent = `$${subtotal.toFixed(2)}`; 
        }

        function addItem(id, name, price, stock) {
            stock = parseInt(stock) || 0; 
            if (stock <= 0) {
                const cardElement = productListDiv ? productListDiv.querySelector(`.product-card[data-id="${id}"]`) : null;
                if(cardElement) {
                    cardElement.style.opacity = '0.5'; 
                    cardElement.style.cursor = 'not-allowed'; 
                }
                return; 
            }
            const newQty = (cart[id] ? cart[id].qty : 0) + 1;
            if (newQty > stock) {
                // CAMBIO 4: Reemplazado alert()
                showAlertModal(`No puedes añadir más de ${stock} unidades de ${name}. Revisa tu stock.`, 'Stock Insuficiente', 'warning');
                cart[id].qty = stock; 
            } else {
                cart[id] = { name, price, qty: newQty, stock };
            }
            updateCartUI();
        }
        
        function removeItem(id) {
            if (cart[id]) {
                const newQty = cart[id].qty - 1;
                setCartQuantity(id, newQty); 
            }
        }

        // ==========================================================
        // LISTENERS DEL CARRITO (Sin cambios)
        // ==========================================================
        if (cartDiv) { 
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
            cartDiv.addEventListener('change', function(e) {
                if (e.target.classList.contains('cart-item-qty')) {
                    const id = e.target.dataset.id;
                    const newQty = e.target.value;
                    setCartQuantity(id, newQty); 
                }
            });
        }
        if (productListDiv) { 
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
        }

        // ==========================================================
        // LÓGICA DE FILTROS Y CLIENTES (Sin cambios)
        // ==========================================================
        function filterProducts() { 
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : ''; 
            const productItems = productListDiv ? productListDiv.querySelectorAll('.product-item') : [];
            productItems.forEach(item => {
                const productNameElement = item.querySelector('.product-name');
                const productName = productNameElement ? productNameElement.textContent.toLowerCase() : '';
                const itemCategoryId = item.dataset.categoryId;
                const categoryMatch = (currentCategoryId === 'all' || itemCategoryId === currentCategoryId);
                const searchMatch = (searchTerm === '' || productName.includes(searchTerm));
                item.style.display = (categoryMatch && searchMatch) ? 'block' : 'none'; 
            });
        }
        categoryFilters.forEach(button => { 
            button.addEventListener('click', function() {
                categoryFilters.forEach(btn => {btn.classList.remove('active', 'btn-outline-dark'); btn.classList.add('btn-outline-secondary');});
                this.classList.add('active', 'btn-outline-dark');
                this.classList.remove('btn-outline-secondary');
                currentCategoryId = this.dataset.categoryId;
                filterProducts(); 
            });
        });
        if (searchInput) { searchInput.addEventListener('input', filterProducts); }
        populateClientDropdown(); // <-- Llamada movida al final
        updateSelectedClient(null, 'Público General'); // <-- Llamada movida al final
        function populateClientDropdown() { 
            if (!clientDropdownMenu) return; 
            const items = clientDropdownMenu.querySelectorAll('li:not(:first-child):not(:nth-child(2))');
            items.forEach(item => item.remove());
            if (clients && clients.length > 0) { 
                clients.forEach(client => {
                    if (client.idCli !== 1) { 
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item select-client';
                        a.href = '#';
                        a.dataset.clientId = client.idCli; 
                        a.dataset.clientName = client.Nombre; 
                        a.textContent = client.Nombre;
                        li.appendChild(a);
                        clientDropdownMenu.appendChild(li);
                    }
                });
            } else {
                const li = document.createElement('li');
                li.innerHTML = '<span class="dropdown-item text-muted">No hay clientes</span>';
                clientDropdownMenu.appendChild(li);
            }
        }
        function updateSelectedClient(id, name) { 
            selectedClientId = id ? parseInt(id) : null;
            if(temporalClientInput){
                temporalClientInput.value = (id || name === 'Público General') ? name : ''; 
                temporalClientInput.placeholder = 'Público General'; 
                if (!id && name === 'Público General') { 
                    temporalClientInput.value = '';
                }
            }
        }
        if(clientDropdownMenu){ clientDropdownMenu.addEventListener('click', function(e) { 
            e.preventDefault();
            if (e.target.classList.contains('select-client')) {
                const clientId = e.target.dataset.clientId;
                const clientName = e.target.dataset.clientName;
                updateSelectedClient(clientId, clientName);
            }
        }); }
        if(temporalClientInput){ temporalClientInput.addEventListener('input', function() { 
            const typedName = this.value.trim();
            const existingClient = clients.find(c => c.Nombre.toLowerCase() === typedName.toLowerCase());
            selectedClientId = existingClient ? existingClient.idCli : null; 
        }); }
        
        // ==========================================================
        // CAMBIO 5: Reemplazado confirm() en 'cancel-order'
        // ==========================================================
        if(cancelButton){ 
            cancelButton.addEventListener('click', function() { 
                if (Object.keys(cart).length > 0) {
                    showConfirmationModal(
                        'Cancelar Orden',
                        '¿Estás seguro de que quieres cancelar esta orden y vaciar el carrito?',
                        'Sí, Cancelar',
                        'btn-danger',
                        function() { // Callback con la lógica original
                            for (const id in cart) { delete cart[id]; }
                            updateSelectedClient(null, 'Público General'); 
                            updateCartUI();
                        }
                    );
                }
            }); 
        }

        // ==========================================================
        // LÓGICA DEL MODAL DE PAGO
        // ==========================================================
        if (paymentModalElement) {
            paymentModalElement.addEventListener('show.bs.modal', function() {
                if (Object.keys(cart).length === 0) {
                    if(paymentModal) paymentModal.hide(); 
                    return;
                }
                if(modalMontoRecibido) modalMontoRecibido.value = ''; 
                if(modalMontoRecibido) modalMontoRecibido.placeholder = '0.00';
                if(modalFolioPago) modalFolioPago.value = ''; 
                if(modalMetodoPago) modalMetodoPago.value = 'efectivo'; 
                togglePaymentFields(); 
                calculateChange(); 
                setTimeout(() => {
                    if(modalMetodoPago && modalMetodoPago.value === 'efectivo' && modalMontoRecibido) {
                        modalMontoRecibido.focus(); 
                    }
                }, 150); 
            });
        }
        if (modalMetodoPago) { modalMetodoPago.addEventListener('change', togglePaymentFields); }
        function togglePaymentFields() { 
            if (!modalMetodoPago || !efectivoFields || !tarjetaFields) return;
            const isCash = modalMetodoPago.value === 'efectivo';
            efectivoFields.style.display = isCash ? 'block' : 'none';
            tarjetaFields.style.display = isCash ? 'none' : 'block'; 
            if (!isCash) {
                if(modalMontoRecibido && totalSpan) modalMontoRecibido.value = totalSpan.textContent.replace('$', ''); 
                calculateChange(); 
            } else {
                 if(modalMontoRecibido) modalMontoRecibido.value = ''; 
                 calculateChange(); 
            }
        }
        if (modalMontoRecibido) { modalMontoRecibido.addEventListener('input', calculateChange); }
        if (modalFolioPago) { modalFolioPago.addEventListener('input', calculateChange); } 
        function calculateChange() { 
            if (!modalMetodoPago || !modalCambioDisplay || !confirmPaymentBtn || !totalSpan) return; 
            const metodo = modalMetodoPago.value;
            const total = parseFloat(totalSpan.textContent.replace('$', ''));
            if (metodo === 'efectivo') {
                const recibido = modalMontoRecibido ? (parseFloat(modalMontoRecibido.value) || 0) : 0;
                const cambio = recibido - total;
                modalCambioDisplay.textContent = `$${Math.max(0, cambio).toFixed(2)}`; 
                confirmPaymentBtn.disabled = (recibido < total); 
            } else if (metodo === 'tarjeta') {
                modalCambioDisplay.textContent = '$0.00'; 
                const folio = modalFolioPago ? modalFolioPago.value.trim() : '';
                confirmPaymentBtn.disabled = (folio === ''); 
            }
        }
        
        // ==========================================================
        // LÓGICA DE PROCESAR PAGO ("Cobrar Ahora")
        // ==========================================================
        if (confirmPaymentBtn) { 
            confirmPaymentBtn.addEventListener('click', async function() { 
                if (Object.keys(cart).length === 0 || !totalSpan) return;

                const detalles = Object.keys(cart).map(id => ({ 
                    producto_id: id, cantidad: cart[id].qty, precio_unitario: cart[id].price, importe: cart[id].price * cart[id].qty 
                }));
                const total = parseFloat(totalSpan.textContent.replace('$', ''));
                const metodoPago = modalMetodoPago ? modalMetodoPago.value : 'efectivo';
                let montoRecibido = modalMontoRecibido ? (parseFloat(modalMontoRecibido.value) || 0) : total;
                let montoEntregado = 0;
                const folioTarjeta = modalFolioPago ? modalFolioPago.value.trim() : null;

                if (metodoPago === 'efectivo') {
                    montoEntregado = Math.max(0, montoRecibido - total); 
                    if (montoRecibido < total) {
                        // CAMBIO 6: Reemplazado alert()
                        showAlertModal('Monto recibido insuficiente.', 'Error de Pago');
                        if(modalMontoRecibido) modalMontoRecibido.focus();
                        return; 
                    }
                } else if (metodoPago === 'tarjeta') {
                    montoRecibido = total; 
                    if (!folioTarjeta) { 
                        // CAMBIO 7: Reemplazado alert()
                        showAlertModal('Por favor, ingrese el folio o número de autorización.', 'Error de Pago');
                        if(modalFolioPago) modalFolioPago.focus();
                        return;
                    }
                }
                
                const payload = {
                    _token: csrfToken, cliente_id: selectedClientId, metodo_pago: metodoPago,
                    total: total, monto_recibido: montoRecibido, monto_entregado: montoEntregado,
                    detalles: detalles,
                    status: 'Pagada'
                };

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';
                
                try {
                    const response = await fetch("{{ route('ventas.store') }}", { 
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json(); 

                    if (response.ok) {
                        if(paymentModal) paymentModal.hide(); 
                        const printUrl = `{{ url('/ventas/imprimir') }}/${result.venta_id}`;
                        if (printFrame) {
                            printFrame.src = printUrl; 
                        } else {
                            console.error("El iframe de impresión no se encontró.");
                        }
                        for (const id in cart) { delete cart[id]; }
                        updateSelectedClient(null, 'Público General'); 
                        updateCartUI();
                    } else { 
                        let errMsg = result.message || 'Error.';
                        if (result.errors) { errMsg += '\nDetalles:\n'; for(const f in result.errors) {errMsg += `- ${result.errors[f].join(', ')}\n`;} }
                        // CAMBIO 8: Reemplazado alert()
                        showAlertModal(errMsg, 'Error al Guardar Venta');
                    }
                } catch (e) { 
                    console.error('Error al procesar venta:', e); 
                    // CAMBIO 9: Reemplazado alert()
                    showAlertModal('Error de conexión o problema en el script. Revise la consola.', 'Error de Red');
                } 
                finally {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-check-circle me-2"></i> Confirmar Pago';
                }
            }); 
        } 

        
        // ==========================================================
        // LÓGICA PROCESAR TICKET PENDIENTE
        // ==========================================================
        if (btnGenerarTicket) {
            btnGenerarTicket.addEventListener('click', function() {
                if (Object.keys(cart).length === 0 || !totalSpan) return;

                // CAMBIO 10: Reemplazado confirm()
                showConfirmationModal(
                    'Generar Ticket',
                    '¿Generar ticket para pagar en caja? La venta quedará como pendiente.',
                    'Sí, Generar Ticket',
                    'btn-primary',
                    async function() { // Inicio del callback async
                        
                        const detalles = Object.keys(cart).map(id => ({ 
                            producto_id: id, cantidad: cart[id].qty, precio_unitario: cart[id].price, importe: cart[id].price * cart[id].qty 
                        }));
                        const total = parseFloat(totalSpan.textContent.replace('$', ''));
                        const payload = {
                            _token: csrfToken, 
                            cliente_id: selectedClientId, 
                            metodo_pago: 'pendiente', 
                            total: total, 
                            monto_recibido: 0, 
                            monto_entregado: 0,
                            detalles: detalles,
                            status: 'Pendiente' 
                        };

                        btnGenerarTicket.disabled = true;
                        if (processButton) processButton.disabled = true;
                        btnGenerarTicket.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generando...';
                        
                        try {
                            const response = await fetch("{{ route('ventas.store') }}", { 
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify(payload)
                            });
                            const result = await response.json(); 

                            if (response.ok) {
                                const printUrl = `{{ url('/ventas/imprimir') }}/${result.venta_id}`;
                                if (printFrame) {
                                    printFrame.src = printUrl; 
                                } else {
                                    console.error("El iframe de impresión no se encontró.");
                                }
                                for (const id in cart) { delete cart[id]; }
                                updateSelectedClient(null, 'Público General'); 
                                updateCartUI();
                            
                            } else { 
                                let errMsg = result.message || 'Error.';
                                if (result.errors) { errMsg += '\nDetalles:\n'; for(const f in result.errors) {errMsg += `- ${result.errors[f].join(', ')}\n`;} }
                                // CAMBIO 11: Reemplazado alert()
                                showAlertModal(errMsg, 'Error al Generar Ticket');
                            }
                        } catch (e) { 
                            console.error('Error al procesar venta pendiente:', e); 
                            // CAMBIO 12: Reemplazado alert()
                            showAlertModal('Error de conexión o problema en el script. Revise la consola.', 'Error de Red');
                        } 
                        finally {
                            btnGenerarTicket.disabled = false;
                            btnGenerarTicket.innerHTML = '<i class="fas fa-ticket-alt me-2"></i> Generar Ticket (Pagar en Caja)';
                            updateCartUI(); 
                        }
                    } // Fin del callback async
                ); // Fin de showConfirmationModal
            });
        } 


        // Inicializar
        updateCartUI();
        populateClientDropdown();
        updateSelectedClient(null, 'Público General');

    }); // Fin DOMContentLoaded
</script>

<style>
/* Estilos */
.product-card:hover { 
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    transition: all 0.2s ease-in-out;
}
.fs-sm { 
    font-size: 0.85rem; 
} 
/* Estilo para el input de cantidad en el carrito */
.cart-item-qty {
    -moz-appearance: textfield; /* Para Firefox */
}
.cart-item-qty::-webkit-outer-spin-button,
.cart-item-qty::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>

@endsection