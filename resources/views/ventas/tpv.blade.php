@extends('layouts.app')

@section('content')
<div class="container-fluid" style="height: calc(100vh - 76px);"> 
    
    @if ($cajaAbierta) 
        <div class="row h-100">
            <!-- 1. Columna de Productos y Categorías (8/12) -->
            <div class="col-lg-8 d-flex flex-column h-100">
                 {{-- Contenido de productos, búsqueda, categorías --}}
                 <h4 class="mb-3 text-primary"><i class="fas fa-bread-slice me-2"></i> Productos Disponibles</h4>
                
                 {{-- Barra de Búsqueda --}}
                 <div class="input-group mb-3 shadow-sm">
                     <span class="input-group-text"><i class="fas fa-search"></i></span>
                     <input type="search" id="product-search" class="form-control" placeholder="Buscar producto por nombre...">
                 </div>
 
                 {{-- Filtro de Categorías --}}
                 <div class="d-flex mb-3 overflow-auto pb-2 border-bottom">
                      <button class="btn btn-sm btn-outline-dark me-2 active category-filter" data-category-id="all">Todas</button>
                      @foreach ($categorias as $cat)
                          <button class="btn btn-sm btn-outline-secondary me-2 category-filter" data-category-id="{{ $cat->id }}">{{ $cat->nombre }}</button>
                      @endforeach
                  </div>
 
                 {{-- Lista de Productos --}}
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

            <!-- 2. Columna del Carrito y Pago (4/12) -->
            <div class="col-lg-4 d-flex flex-column border-start ps-4 h-100">
                <h4 class="mb-3 text-danger"><i class="fas fa-shopping-cart me-2"></i> Orden Actual</h4>
                
                {{-- Cliente Editable con Dropdown --}}
                <div class="alert alert-info py-2 mb-3">
                    <div><small>Cajero: <strong>{{ Auth::user()->name }}</strong></small></div>
                    <hr class="my-1">
                    <div class="d-flex align-items-center">
                        <small class="me-2">Cliente:</small> 
                        <div class="input-group input-group-sm flex-grow-1">
                            {{-- Input para escribir nombre temporal o mostrar seleccionado --}}
                            <input type="text" id="temporal-client-name" class="form-control" placeholder="Público General">
                            {{-- Botón que abre el dropdown --}}
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Seleccionar Cliente Existente"></button>
                            {{-- Menú dropdown que se llenará con JS --}}
                            <ul class="dropdown-menu dropdown-menu-end" id="client-dropdown-menu">
                                <li><a class="dropdown-item select-client" href="#" data-client-id="" data-client-name="Público General">Público General</a></li>
                                <li><hr class="dropdown-divider"></li>
                                {{-- Los clientes de la BD se añadirán aquí --}}
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

{{-- MODAL PARA SELECCIONAR CLIENTE (Opcional, se puede quitar si no se usa) --}}
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables de Estado
        const cart = {}; 
        let selectedClientId = null; 
        let currentCategoryId = 'all'; 
        
        // Convertir clientes PHP a JS (Asegurarse que $clientes se pasa desde el controlador)
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const emptyCartMessageHTML = '<p class="text-center text-muted empty-cart-message">Añada productos para comenzar la venta.</p>';

        // Función updateCartUI
         function updateCartUI() {
            let subtotal = 0;
            let itemCount = 0;
            cartDiv.innerHTML = ''; 
            
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
                 if (!cartDiv.querySelector('.cart-item')) { 
                     cartDiv.innerHTML = emptyCartMessageHTML;
                 }
                processButton.classList.add('disabled');
            } else {
                 const emptyMsg = cartDiv.querySelector('.empty-cart-message');
                 if(emptyMsg) emptyMsg.remove();
                processButton.classList.remove('disabled');
            }

            subtotalSpan.textContent = `$${subtotal.toFixed(2)}`;
            totalSpan.textContent = `$${subtotal.toFixed(2)}`;
        }

        // Función addItem
         function addItem(id, name, price, stock) {
            if (!stock || stock <= 0) {
                const cardElement = productListDiv.querySelector(`.product-card[data-id="${id}"]`);
                if(cardElement) cardElement.style.opacity = '0.5'; 
                // alert(`El producto ${name} está agotado.`); // Evitar alertas molestas
                return;
            }
            
            if (cart[id]) {
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
        
        // Función removeItem
         function removeItem(id) {
            if (cart[id]) {
                cart[id].qty--;
                if (cart[id].qty <= 0) {
                    delete cart[id]; 
                }
            }
            updateCartUI();
        }

        // Manejador de eventos en el carrito
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

        // Evento para añadir productos al carrito
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

        // Función para filtrar productos
         function filterProducts() {
             const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : ''; 
             const productItems = productListDiv.querySelectorAll('.product-item');

             productItems.forEach(item => {
                 const productNameElement = item.querySelector('.product-name');
                 const productName = productNameElement ? productNameElement.textContent.toLowerCase() : '';
                 const itemCategoryId = item.dataset.categoryId;
                 const categoryMatch = (currentCategoryId === 'all' || itemCategoryId === currentCategoryId);
                 const searchMatch = (searchTerm === '' || productName.includes(searchTerm));

                 if (categoryMatch && searchMatch) {
                     item.style.display = 'block'; 
                 } else {
                     item.style.display = 'none'; 
                 }
             });
         }


        // Evento para Filtro de Categorías
         categoryFilters.forEach(button => {
            button.addEventListener('click', function() {
                categoryFilters.forEach(btn => btn.classList.remove('active', 'btn-outline-dark'));
                categoryFilters.forEach(btn => btn.classList.add('btn-outline-secondary'));
                this.classList.add('active', 'btn-outline-dark');
                this.classList.remove('btn-outline-secondary');
                currentCategoryId = this.dataset.categoryId;
                filterProducts(); 
            });
        });

        // Evento para la Barra de Búsqueda
         if (searchInput) {
            searchInput.addEventListener('input', filterProducts); 
        }

        // Función para poblar el dropdown de clientes
        function populateClientDropdown() {
             const items = clientDropdownMenu.querySelectorAll('li:not(:first-child):not(:nth-child(2))');
             items.forEach(item => item.remove());

             if (clients && clients.length > 0) { // Verificar que clients exista y tenga elementos
                 clients.forEach(client => {
                     const li = document.createElement('li');
                     const a = document.createElement('a');
                     a.className = 'dropdown-item select-client';
                     a.href = '#';
                     a.dataset.clientId = client.idCli; // Usar idCli de tu modelo Cliente
                     a.dataset.clientName = client.Nombre; // Usar Nombre de tu modelo Cliente
                     a.textContent = client.Nombre;
                     li.appendChild(a);
                     clientDropdownMenu.appendChild(li);
                 });
             } else {
                 // Opcional: Mostrar mensaje si no hay clientes
                 const li = document.createElement('li');
                 li.innerHTML = '<span class="dropdown-item text-muted">No hay clientes registrados</span>';
                 clientDropdownMenu.appendChild(li);
             }
         }
        
        // Función para actualizar cliente seleccionado
        function updateSelectedClient(id, name) {
            selectedClientId = id ? parseInt(id) : null;
            temporalClientInput.value = (id || name === 'Público General') ? name : ''; // Poner nombre solo si es existente o P.G.
            temporalClientInput.placeholder = 'Público General'; 
            if (!id && name === 'Público General') { // Resetear a placeholder si es P.G.
                temporalClientInput.value = '';
            }
        }
        
        // Evento listener para el dropdown de clientes
         clientDropdownMenu.addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.classList.contains('select-client')) {
                const clientId = e.target.dataset.clientId;
                const clientName = e.target.dataset.clientName;
                updateSelectedClient(clientId, clientName);
            }
        });

        // Evento para el Input de Cliente
         temporalClientInput.addEventListener('input', function() {
            const typedName = this.value.trim();
            const existingClient = clients.find(c => c.Nombre.toLowerCase() === typedName.toLowerCase());

            if (existingClient) {
                selectedClientId = existingClient.idCli;
            } else {
                selectedClientId = null; // Si no coincide, es temporal o Público General
            }
        });

        // Evento para cancelar la orden
         cancelButton.addEventListener('click', function() {
            if (Object.keys(cart).length > 0 && confirm('¿Está seguro de cancelar la orden actual y vaciar el carrito?')) {
                for (const id in cart) { delete cart[id]; }
                updateSelectedClient(null, 'Público General'); // Resetear a Público General
                updateCartUI();
            }
        });
        
        // Evento para procesar el pago
         processButton.addEventListener('click', async function() {
             if (Object.keys(cart).length === 0 || this.classList.contains('disabled')) return;
             
             // Aquí iría la lógica del modal de pago (selección método, monto, cambio)
             // ...

             const detalles = Object.keys(cart).map(id => ({ 
                 producto_id: id,
                 cantidad: cart[id].qty,
                 precio_unitario: cart[id].price,
                 importe: cart[id].price * cart[id].qty
             }));
             const total = parseFloat(totalSpan.textContent.replace('$', ''));
             const metodoPagoSimulado = 'efectivo'; 
             
             const payload = {
                 _token: csrfToken,
                 cliente_id: selectedClientId, // Correcto
                 metodo_pago: metodoPagoSimulado,
                 total: total,
                 monto_recibido: total, // Simulado
                 monto_entregado: 0, // Simulado
                 detalles: detalles
             };

             // Deshabilitar botón
             this.disabled = true;
             this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

             // Fetch al backend
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
                     alert('Venta procesada exitosamente! ID: ' + (result.venta_id || 'N/A'));
                     for (const id in cart) { delete cart[id]; }
                     updateSelectedClient(null, 'Público General'); 
                     updateCartUI();
                     // Recargar para actualizar stock visualmente
                     window.location.reload(); 
                 } else {
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
                  this.disabled = false;
                  this.innerHTML = '<i class="fas fa-money-check-alt me-2"></i> Procesar Venta';
             }
         });

        // Inicializar la interfaz al cargar
        updateCartUI();
        populateClientDropdown(); // Llenar el dropdown
        updateSelectedClient(null, 'Público General'); // Iniciar con Público General

    }); // Fin de DOMContentLoaded
</script>

<style>
/* Estilos adicionales */
.product-card:hover { 
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,.12)!important;
    transition: all 0.2s ease-in-out;
 }
.fs-sm { font-size: 0.8rem; } 
</style>
@endsection

