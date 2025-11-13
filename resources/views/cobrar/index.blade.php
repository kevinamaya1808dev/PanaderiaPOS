@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Cobrar Ventas Pendientes</h2>

    <!-- 1. Campo de Búsqueda de Folio (Para tickets perdidos) -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="input-group input-group-lg shadow-sm mb-3">
                <span class="input-group-text"><i class="fas fa-ticket-alt"></i></span>
                <input type="number" class="form-control" id="folio-search-input" placeholder="Ingresar Folio (ID) del Ticket...">
                <button class="btn btn-primary" id="folio-search-btn">
                    <i class="fas fa-search me-2"></i> Buscar Manualmente
                </button>
            </div>
            <div id="search-error" class="alert alert-danger" style="display: none;"></div>
        </div>
    </div>

    <!-- 2. Área de Resultados (Sigue igual) -->
    <div id="venta-encontrada-card" class="card shadow-lg mt-3" style="display: none;">
        {{-- Esta tarjeta se llena con JavaScript cuando se busca o selecciona --}}
    </div>

    <!-- 3. ¡NUEVO! Cola de Ventas Pendientes -->
    <hr class="my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Cola de Pagos Pendientes</h4>
        <div id="loading-spinner" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
            <span class="visually-hidden">Actualizando...</span>
        </div>
    </div>
    
    <div id="lista-pendientes-wrapper" class="card shadow-sm">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover mb-0">
                <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Embolsador</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="lista-pendientes-body">
                    {{-- La lista inicial se carga con PHP --}}
                    @forelse($ventasPendientes as $venta)
                        <tr>
                            <td class="fw-bold">{{ $venta->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y') }}</td>
                            <td>{{ $venta->user->name ?? 'N/A' }}</td>
                            <td>
                                <ul class="list-unstyled mb-0" style="font-size: 0.9em;">
                                    @foreach($venta->detalles as $detalle)
                                        <li>{{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? '?' }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="fw-bold">${{ number_format($venta->total, 2) }}</td>
                            <td>
                                {{-- Este botón ahora pasa el ID a buscarVenta() --}}
                                <button class="btn btn-sm btn-warning btn-cobrar-lista" data-folio="{{ $venta->id }}">
                                    Cobrar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-pendientes-row">
                            <td colspan="6" class="text-center text-muted p-3">No hay ventas pendientes por el momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


{{-- ========================================================== --}}
{{-- MODAL DE PAGO (Sin cambios) --}}
{{-- ========================================================== --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-cash-register me-2"></i> Registrar Pago de Folio <span id="modal-folio-display"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 text-center">
                    <label class="form-label fs-5">Total a Pagar:</label>
                    <div class="fs-1 fw-bolder text-danger" id="modal-total-display">$0.00</div>
                </div>
                
                {{-- (Campos del modal: efectivo, tarjeta, etc. - Sin cambios) --}}
                <div class="mb-3">
                    <label for="modal-metodo-pago" class="form-label fw-bold">Método de Pago</label>
                    <select class="form-select form-select-lg" id="modal-metodo-pago">
                        <option value="efectivo" selected>Efectivo</option>
                        <option value="tarjeta">Tarjeta</option> 
                    </select>
                </div>
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

{{-- ***** IFRAME OCULTO (Sin cambios) ***** --}}
<iframe id="print-frame" name="printFrame" style="display: none; border: 0;"></iframe>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Referencias del DOM ---
    const searchInput = document.getElementById('folio-search-input');
    const searchBtn = document.getElementById('folio-search-btn');
    const searchError = document.getElementById('search-error');
    const ventaCard = document.getElementById('venta-encontrada-card');
    
    // --- ¡NUEVAS REFERENCIAS! ---
    const listaPendientesBody = document.getElementById('lista-pendientes-body');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    // Referencias de la Tarjeta (se llenan dinámicamente)
    // (Ahora la tarjeta se crea desde JS)

    // Referencias del Modal (Copiadas del TPV)
    let paymentModal = null;
    const paymentModalElement = document.getElementById('paymentModal');
    if (paymentModalElement) {
        paymentModal = new bootstrap.Modal(paymentModalElement);
    }
    const modalFolioDisplay = document.getElementById('modal-folio-display');
    const modalTotalDisplay = document.getElementById('modal-total-display');
    const modalMetodoPago = document.getElementById('modal-metodo-pago');
    const modalMontoRecibido = document.getElementById('modal-monto-recibido');
    const modalCambioDisplay = document.getElementById('modal-cambio-display');
    const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
    const efectivoFields = document.getElementById('efectivo-fields'); 
    const tarjetaFields = document.getElementById('tarjeta-fields'); 
    const modalFolioPago = document.getElementById('modal-folio-pago'); 
    const printFrame = document.getElementById('print-frame');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let ventaEncontrada = null; // Guardará el objeto Venta

    // --- Lógica de Búsqueda (AHORA ACEPTA UN FOLIO) ---
    searchBtn.addEventListener('click', () => buscarVenta(null)); // El botón usa el input
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarVenta(null);
        }
    });

    async function buscarVenta(folio = null) {
        const folioParaBuscar = folio || searchInput.value;
        if (!folioParaBuscar) return;

        resetearVista();
        
        try {
            const response = await fetch(`{{ route('cobrar.buscar') }}?folio=${folioParaBuscar}`);
            const data = await response.json();

            if (!response.ok) {
                mostrarError(data.error || 'Error al buscar la venta.');
            } else {
                ventaEncontrada = data; // Guardamos la venta
                mostrarDatosVenta(data);
                // Hacer scroll y resaltar
                window.scrollTo({ top: 0, behavior: 'smooth' });
                ventaCard.classList.add('border-warning');
            }
        } catch (e) {
            mostrarError('Error de conexión. Intente de nuevo.');
            console.error(e);
        }
    }

    function mostrarError(mensaje) {
        searchError.textContent = mensaje;
        searchError.style.display = 'block';
    }

    function resetearVista() {
        ventaCard.style.display = 'none';
        ventaCard.classList.remove('border-warning');
        searchError.style.display = 'none';
        ventaEncontrada = null;
    }

    // Esta función ahora construye el HTML de la tarjeta
    function mostrarDatosVenta(venta) {
        // Formatear productos
        let productosHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.9em;">';
        venta.detalles.forEach(detalle => {
            productosHtml += `<li>${detalle.cantidad} x ${detalle.producto.nombre} ($${parseFloat(detalle.importe).toFixed(2)})</li>`;
        });
        productosHtml += '</ul>';

        // Llenar el HTML de la tarjeta
        ventaCard.innerHTML = `
            <div class="card-header bg-warning">
                <h4 class="mb-0">Venta Pendiente de Pago</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <h5 class="mb-3">Detalles de la Orden</h5>
                        <p><strong>Folio:</strong> <span id="folio-display">${venta.id}</span></p>
                        <p><strong>Generada por:</strong> <span id="cajero-origen-display">${venta.user.name || 'N/A'}</span></p>
                        <p><strong>Cliente:</strong> <span id="cliente-display">${venta.cliente ? venta.cliente.Nombre : 'Público General'}</span></p>
                        <hr>
                        <h6>Productos:</h6>
                        <div id="productos-list" style="max-height: 200px; overflow-y: auto;">
                            ${productosHtml}
                        </div>
                    </div>
                    <div class="col-md-5 border-start">
                        <h5 class="text-danger">Total a Pagar:</h5>
                        <div class="fs-1 fw-bolder text-danger mb-4" id="total-display">$${parseFloat(venta.total).toFixed(2)}</div>
                        <button id="abrir-modal-pago-btn" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fas fa-dollar-sign me-2"></i> Cobrar Esta Venta
                        </button>
                    </div>
                </div>
            </div>`;
        
        // Mostrar la tarjeta
        ventaCard.style.display = 'block';

        // Llenar el modal (prepararlo)
        modalFolioDisplay.textContent = `#${venta.id}`;
        modalTotalDisplay.textContent = `$${parseFloat(venta.total).toFixed(2)}`;
        
        modalMetodoPago.value = 'efectivo';
        modalMontoRecibido.value = '';
        modalFolioPago.value = '';
        togglePaymentFields();
        calculateChange();
    }

    // --- Lógica del Modal de Pago (Sin cambios) ---
    modalMetodoPago.addEventListener('change', togglePaymentFields);
    modalMontoRecibido.addEventListener('input', calculateChange);
    modalFolioPago.addEventListener('input', calculateChange); 

    function togglePaymentFields() { 
        const isCash = modalMetodoPago.value === 'efectivo';
        efectivoFields.style.display = isCash ? 'block' : 'none';
        tarjetaFields.style.display = isCash ? 'none' : 'block'; 
        if (!isCash) {
            modalMontoRecibido.value = ventaEncontrada ? ventaEncontrada.total : '0.00'; 
            calculateChange(); 
        } else {
             modalMontoRecibido.value = ''; 
             calculateChange(); 
        }
    }

    function calculateChange() { 
        if (!ventaEncontrada) return;
        const metodo = modalMetodoPago.value;
        const total = parseFloat(ventaEncontrada.total);

        if (metodo === 'efectivo') {
            const recibido = parseFloat(modalMontoRecibido.value) || 0;
            const cambio = recibido - total;
            modalCambioDisplay.textContent = `$${Math.max(0, cambio).toFixed(2)}`; 
            confirmPaymentBtn.disabled = (recibido < total); 
        } else if (metodo === 'tarjeta') {
            modalCambioDisplay.textContent = '$0.00'; 
            const folio = modalFolioPago.value.trim();
            confirmPaymentBtn.disabled = (folio === ''); 
        }
    }

    // --- Lógica de Confirmar Pago (Sin cambios) ---
    confirmPaymentBtn.addEventListener('click', async function() {
        if (!ventaEncontrada) return;

        const total = parseFloat(ventaEncontrada.total);
        const metodoPago = modalMetodoPago.value;
        let montoRecibido = parseFloat(modalMontoRecibido.value) || 0;
        let montoEntregado = 0;
        const folioTarjeta = modalFolioPago.value.trim(); 

        if (metodoPago === 'efectivo') {
            montoEntregado = Math.max(0, montoRecibido - total); 
            if (montoRecibido < total) {
                alert('Monto recibido insuficiente.'); return; 
            }
        } else {
            montoRecibido = total; 
            montoEntregado = 0;
            if (!folioTarjeta) {
                alert('Por favor, ingrese el folio o número de autorización.'); return;
            }
        }
        
        const payload = {
            _token: csrfToken,
            venta_id: ventaEncontrada.id,
            metodo_pago: metodoPago,
            monto_recibido: montoRecibido,
            monto_entregado: montoEntregado,
            folio_tarjeta: folioTarjeta
        };

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

        try {
            const response = await fetch(`{{ route('cobrar.pagar') }}`, { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const result = await response.json(); 

            if (response.ok) {
                if (paymentModal) paymentModal.hide();
                alert(result.message); 
                location.reload();

            } else { 
                alert('Error: \n' + (result.error || 'Error desconocido'));
            }
        } catch (e) { 
            console.error('Error al procesar el pago:', e); 
            alert('Error de conexión.');
        } 
        finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i> Confirmar Pago';
        }
    });
    
    // --- ¡NUEVA LÓGICA DE POLLING! ---
    
    // 1. Event listener para la tabla (delegación)
    listaPendientesBody.addEventListener('click', function(e) {
        // e.target es el elemento exacto (ej. el <i class="...
        // e.target.closest(...) busca el botón más cercano
        const targetButton = e.target.closest('.btn-cobrar-lista');
        if (targetButton) {
            const folio = targetButton.dataset.folio;
            buscarVenta(folio); // Llama a la función de búsqueda
        }
    });

    // 2. Función para renderizar la lista
    function renderListaPendientes(ventas) {
        listaPendientesBody.innerHTML = ''; // Limpiar la tabla
        if (ventas.length === 0) {
            listaPendientesBody.innerHTML = `
                <tr id="no-pendientes-row">
                    <td colspan="6" class="text-center text-muted p-3">No hay ventas pendientes por el momento.</td>
                </tr>`;
        } else {
            ventas.forEach(venta => {
                let productosHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.9em;">';
                venta.detalles.forEach(detalle => {
                    productosHtml += `<li>${detalle.cantidad} x ${detalle.producto.nombre ?? '?'}</li>`;
                });
                productosHtml += '</ul>';

                const tr = document.createElement('tr');
               tr.innerHTML = `
                    <td class="fw-bold">${venta.id}</td>
                    <td>${new Date(venta.fecha_hora).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' })}</td>
                    <td>${venta.user.name ?? 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btn-cobrar-lista" data-folio="${venta.id}">
                            Cobrar
                        </button>
                    </td>
                `;
                listaPendientesBody.appendChild(tr);
            });
        }
    }

    // 3. Función para cargar la lista
    async function cargarListaPendientes() {
        loadingSpinner.style.display = 'block'; // Mostrar spinner
        try {
            const response = await fetch(`{{ route('cobrar.listaPendientes') }}`);
            if (response.ok) {
                const ventas = await response.json();
                renderListaPendientes(ventas); // Dibujar la tabla
            }
        } catch (e) {
            console.error("Error al refrescar la lista:", e);
        } finally {
            loadingSpinner.style.display = 'none'; // Ocultar spinner
        }
    }

    // 4. Iniciar el Polling (Refresco automático)
    // Refresca la lista cada 10 segundos (10000 milisegundos)
    setInterval(cargarListaPendientes, 10000); 

});
</script>
@endpush