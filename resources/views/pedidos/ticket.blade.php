<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Pedido #{{ $pedido->id }}</title>
    {{-- Importante: Usamos el mismo CSS que el ticket de venta --}}
    <link rel="stylesheet" href="{{ public_path('css/ticket.css') }}">
</head>
<body onload="window.print()">
    <article class="ticket-body">
        
        {{-- 1. ENCABEZADO (Igual al ticket de venta) --}}
        <header class="text-center">
            <h4>Panaderia "JIREH"</h4>
            <p>Calle Allende, Chalco EDOMEX.</p>
            <p>Tel: 621382826</p>
            <p><strong>TICKET DE ENTREGA / PEDIDO</strong></p>
        </header>

        <hr>

        {{-- 2. INFORMACIÓN DEL PEDIDO Y CLIENTE --}}
        <section>
            <p><strong>Folio Pedido:</strong> #{{ $pedido->id }}</p>
            {{-- Usamos la fecha actual de impresión --}}
            <p><strong>Fecha Impresión:</strong> {{ date('d/m/Y h:i A') }}</p>
            
            <p><strong>Cliente:</strong> {{ $pedido->nombre_cliente }}</p>
            
            @if($pedido->telefono_cliente)
                <p><strong>Tel:</strong> {{ $pedido->telefono_cliente }}</p>
            @endif

            @if($pedido->fecha_entrega)
                <p><strong>Fecha Entrega:</strong> {{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') }}</p>
            @endif
        </section>

        <hr>

        {{-- 3. DETALLES DE LOS PRODUCTOS (Sin tablas, usando divs y spans) --}}
        <section>
            <div>
                <span><strong>CANT PRODUCTO</strong></span>
                <span class="float-right"><strong>IMPORTE</strong></span>
            </div>

            @foreach($pedido->detalles as $detalle)
                <div style="margin-bottom: 5px;">
                    {{-- Cantidad y Nombre --}}
                    <span>{{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto' }}</span>
                    
                    {{-- Precio calculado --}}
                    <span class="float-right">${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</span>
                    
                    {{-- Especificaciones (si las hay) en pequeñito --}}
                    @if($detalle->especificaciones)
                        <br>
                        <small style="color: #555; font-size: 0.9em;">({{ $detalle->especificaciones }})</small>
                    @endif
                </div>
            @endforeach
        </section>

        <hr>

        {{-- 4. TOTALES Y PAGOS (Adaptado a la lógica de Anticipo/Liquidación) --}}
        <section>
            {{-- Total General --}}
            <div>
                <span class="float-right"><strong>TOTAL:</strong> ${{ number_format($pedido->total, 2) }}</span>
            </div>

            {{-- Desglose de pagos --}}
            <div style="margin-top: 5px;">
                <span class="float-right">Anticipo: -${{ number_format($pedido->anticipo, 2) }}</span>
            </div>

            @php
                $liquidacion = $pedido->total - $pedido->anticipo;
            @endphp

            @if($liquidacion > 0)
                <div>
                    <span class="float-right">Liquidación: -${{ number_format($liquidacion, 2) }}</span>
                </div>
            @endif

            {{-- Saldo Final --}}
            <div style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
                <span class="float-right"><strong>Saldo Pendiente: $0.00</strong></span>
            </div>
        </section>

        <hr>

        {{-- 5. PIE DE PÁGINA (Igual al ticket de venta) --}}
        <footer class="text-center">
            <p>¡Gracias por su preferencia!</p>
            <p>Vuelva pronto.</p>
            <p>¡Contactanos! www.ollintem.com.mx</p>
            <br>
            <small>*** Copia Cliente ***</small>
        </footer>

    </article>
</body>
</html>