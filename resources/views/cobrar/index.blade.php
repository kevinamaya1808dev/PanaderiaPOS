@extends('layouts.app')

@section('content')
<div class="container">
    
    {{-- Título --}}
    <h2 class="mb-4">
        <i class="fas fa-hand-holding-usd text-secondary me-2"></i> Cobrar Ventas Pendientes
    </h2>

    {{-- Buscador Manual (Estilo Card Suave) --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white"><i class="fas fa-ticket-alt text-muted"></i></span>
                <input type="number" class="form-control border-start-0" id="folio-search-input" placeholder="Ingresar Folio (ID) del Ticket...">
                <button class="btn btn-primary" id="folio-search-btn">
                    <i class="fas fa-search me-2"></i> Buscar Manualmente
                </button>
            </div>
            <div id="search-error" class="alert alert-danger mt-2" style="display: none;"></div>
        </div>
    </div>

    {{-- Tarjeta de Venta Encontrada (Se mantiene oculta hasta buscar) --}}
    <div id="venta-encontrada-card" class="card shadow-lg mt-3 mb-5" style="display: none;">
        {{-- Se llena con JS --}}
    </div>

    <hr class="my-4">

    {{-- Encabezado de la Tabla y Spinner --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Cola de Pagos Pendientes</h4>
        <div id="loading-spinner" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;">
            <span class="visually-hidden">Actualizando...</span>
        </div>
    </div>
    
    {{-- Tabla libre en el contenedor (Estilo Unificado) --}}
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark" style="position: sticky; top: 0; z-index: 1;">
                <tr>
                    <th class="ps-4">Folio</th>
                    <th>Fecha</th>
                    <th>Embolsador</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th class="text-end pe-4">Acción</th>
                </tr>
            </thead>
            <tbody id="lista-pendientes-body">
                {{-- La lista inicial se carga con PHP --}}
                @forelse($ventasPendientes as $venta)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $venta->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y H:i') }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($venta->user->name ?? '?', 0, 1)) }}
                                </div>
                                {{ $venta->user->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <ul class="list-unstyled mb-0 text-muted" style="font-size: 0.85em;">
                                @foreach($venta->detalles as $detalle)
                                    <li>{{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? '?' }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="fw-bold text-danger">${{ number_format($venta->total, 2) }}</td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-warning btn-cobrar-lista shadow-sm" data-folio="{{ $venta->id }}">
                                <i class="fas fa-cash-register me-1"></i> Cobrar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr id="no-pendientes-row">
                        <td colspan="6" class="text-center text-muted p-5">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i><br>
                            No hay ventas pendientes por el momento.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


{{-- ========================================================== --}}
{{-- MODAL DE PAGO --}}
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

{{-- MODAL DE ALERTA --}}
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

{{-- IFRAME OCULTO --}}
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
    
    const listaPendientesBody = document.getElementById('lista-pendientes-body');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    // Referencias del Modal de Pago
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

    let ventaEncontrada = null; 

    // --- Referencias del Modal de Alerta ---
    const alertModalElement = document.getElementById('alertModal');
    let alertModal = null;
    if (alertModalElement) {
        alertModal = new bootstrap.Modal(alertModalElement);
    }
    const alertModalTitle = document.getElementById('alertModalTitle');
    const alertModalBody = document.getElementById('alertModalBody');
    const alertModalHeader = document.getElementById('alertModalHeader');

    // --- Función Helper para Mostrar Alertas ---
    function showAlertModal(body, title = 'Atención', type = 'danger') {
        if (!alertModal) { alert(body); return; }
        alertModalTitle.textContent = title;
        alertModalBody.textContent = body;
        
        alertModalHeader.className = 'modal-header text-white'; 
        if (type === 'danger') alertModalHeader.classList.add('bg-danger');
        else if (type === 'success') alertModalHeader.classList.add('bg-success');
        else if (type === 'warning') {
            alertModalHeader.classList.add('bg-warning');
            alertModalHeader.classList.remove('text-white');
            alertModalHeader.classList.add('text-dark');
        } else alertModalHeader.classList.add('bg-primary');

        alertModal.show();
    }

    // --- Lógica de Búsqueda ---
    searchBtn.addEventListener('click', () => buscarVenta(null)); 
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') buscarVenta(null);
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
                ventaEncontrada = data; 
                mostrarDatosVenta(data);
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

    function mostrarDatosVenta(venta) {
        let productosHtml = '<ul class="list-unstyled mb-0" style="font-size: 0.9em;">';
        venta.detalles.forEach(detalle => {
            productosHtml += `<li>${detalle.cantidad} x ${detalle.producto.nombre} ($${parseFloat(detalle.importe).toFixed(2)})</li>`;
        });
        productosHtml += '</ul>';

        let clienteNombre = 'Público General';
        
        if (venta.cliente) {
            clienteNombre = venta.cliente.Nombre || venta.cliente.nombre || clienteNombre;
        } else if (venta.nombre_cliente) {
            clienteNombre = venta.nombre_cliente;
        }

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
                        
                        <p><strong>Cliente:</strong> <span id="cliente-display">${clienteNombre}</span></p>
                        
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
        
        ventaCard.style.display = 'block';

        modalFolioDisplay.textContent = `#${venta.id}`;
        modalTotalDisplay.textContent = `$${parseFloat(venta.total).toFixed(2)}`;
        
        modalMetodoPago.value = 'efectivo';
        modalMontoRecibido.value = '';
        modalFolioPago.value = '';
        togglePaymentFields();
        calculateChange();
    }

    // --- Lógica del Modal de Pago ---
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

    // --- Lógica de Confirmar Pago ---
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
                showAlertModal('Monto recibido insuficiente.', 'Atención', 'warning');
                return; 
            }
        } else {
            montoRecibido = total; 
            montoEntregado = 0;
            if (!folioTarjeta) {
                showAlertModal('Por favor, ingrese el folio o número de autorización.', 'Falta Información', 'warning');
                return;
            }
        }
        
        // --- CAMBIO IMPORTANTE AQUÍ ---
        const payload = {
            _token: csrfToken,
            venta_id: ventaEncontrada.id,
            metodo_pago: metodoPago,
            monto_recibido: montoRecibido,
            monto_entregado: montoEntregado,
            
            // ANTES: folio_tarjeta: folioTarjeta
            // AHORA: Usamos el nombre que espera el controlador
            referencia_pago: folioTarjeta 
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
                showAlertModal(result.message, '¡Pago Registrado!', 'success');
                alertModalElement.addEventListener('hidden.bs.modal', function () {
                    location.reload();
                }, { once: true });
            } else { 
                let errorMsg = result.error || 'Error desconocido';
                showAlertModal(errorMsg, 'Error al procesar', 'danger');
            }
        } catch (e) { 
            console.error('Error al procesar el pago:', e); 
            showAlertModal('Error de conexión con el servidor.', 'Error de Red', 'danger');
        } 
        finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check-circle me-2"></i> Confirmar Pago';
        }
    });
    
    // --- Lógica de Polling (Clic en botones de lista) ---
    listaPendientesBody.addEventListener('click', function(e) {
        const targetButton = e.target.closest('.btn-cobrar-lista');
        if (targetButton) {
            const folio = targetButton.dataset.folio;
            buscarVenta(folio); 
        }
    });

    // --- Renderizado de Lista (AJAX) ---
    function renderListaPendientes(ventas) {
        listaPendientesBody.innerHTML = ''; 
        if (ventas.length === 0) {
            listaPendientesBody.innerHTML = `
                <tr id="no-pendientes-row">
                    <td colspan="6" class="text-center text-muted p-5">
                         <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i><br>
                        No hay ventas pendientes por el momento.
                    </td>
                </tr>`;
        } else {
            ventas.forEach(venta => {
                let productosHtml = '<ul class="list-unstyled mb-0 text-muted" style="font-size: 0.85em;">';
                venta.detalles.forEach(detalle => {
                    productosHtml += `<li>${detalle.cantidad} x ${detalle.producto.nombre ?? '?'}</li>`;
                });
                productosHtml += '</ul>';

                const fecha = new Date(venta.fecha_hora);
                const fechaStr = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' }) + ' ' +
                                 fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

                const nombre = venta.user.name ?? 'N/A';
                const inicial = nombre.charAt(0).toUpperCase();

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-4 fw-bold">${venta.id}</td>
                    <td>${fechaStr}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                 style="width: 30px; height: 30px; font-size: 0.8rem;">
                                ${inicial}
                            </div>
                            ${nombre}
                        </div>
                    </td>
                    <td>${productosHtml}</td>
                    <td class="fw-bold text-danger">$${parseFloat(venta.total).toFixed(2)}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-warning btn-cobrar-lista shadow-sm" data-folio="${venta.id}">
                            <i class="fas fa-cash-register me-1"></i> Cobrar
                        </button>
                    </td>
                `;
                listaPendientesBody.appendChild(tr);
            });
        }
    }

    async function cargarListaPendientes() {
        loadingSpinner.style.display = 'block'; 
        try {
            const response = await fetch(`{{ route('cobrar.listaPendientes') }}`);
            if (response.ok) {
                const ventas = await response.json();
                renderListaPendientes(ventas); 
            }
        } catch (e) {
            console.error("Error al refrescar la lista:", e);
        } finally {
            loadingSpinner.style.display = 'none'; 
        }
    }

    setInterval(cargarListaPendientes, 10000); 

});
</script>
@endpush