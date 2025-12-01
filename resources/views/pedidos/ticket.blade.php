<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo ticket para alineación */
            font-size: 12px;
            margin: 0;
            padding: 5px;
            width: 80mm; /* Ancho estándar de impresora térmica (ajustable a 58mm) */
            background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        
        /* Línea separadora punteada */
        .line { 
            border-bottom: 1px dashed #000; 
            margin: 8px 0; 
        }
        
        .logo { 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 5px; 
            text-transform: uppercase;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
        }
        
        td, th { 
            padding: 2px 0;
            vertical-align: top; 
        }

        /* Ocultar elementos al imprimir si es necesario */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()"> {{-- Imprimir automáticamente al cargar la página --}}

    <div class="text-center">
        <div class="logo">PANADERÍA KAIROS</div>
        <div>Ticket de Entrega</div>
        <div class="line"></div>
        <div class="text-left">
            <strong>Folio:</strong> #{{ $pedido->id }}<br>
            <strong>Fecha:</strong> {{ date('d/m/Y h:i A') }}<br> {{-- Fecha de impresión --}}
        </div>
    </div>

    <div class="line"></div>

    {{-- Datos del Cliente --}}
    <div>
        <strong>Cliente:</strong> {{ $pedido->nombre_cliente }}<br>
        @if($pedido->telefono_cliente)
            <strong>Tel:</strong> {{ $pedido->telefono_cliente }}<br>
        @endif
        @if($pedido->fecha_entrega)
            <strong>Entrega:</strong> {{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') }}
        @endif
    </div>

    <div class="line"></div>

    {{-- Tabla de Productos --}}
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 15%">Cant.</th>
                <th class="text-left" style="width: 60%">Producto</th>
                <th class="text-right" style="width: 25%">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td class="text-left">
                    {{ $detalle->producto->nombre ?? 'Producto' }}
                    @if($detalle->especificaciones)
                        <br><small>({{ $detalle->especificaciones }})</small>
                    @endif
                </td>
                <td class="text-right">${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- Totales --}}
    <table>
        <tr>
            <td class="text-right">Subtotal:</td>
            <td class="text-right">${{ number_format($pedido->total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">Total:</td>
            <td class="text-right bold" style="font-size: 14px;">${{ number_format($pedido->total, 2) }}</td>
        </tr>
        
        {{-- Desglose de pagos --}}
        <tr>
            <td colspan="2" class="line"></td>
        </tr>
        
        <tr>
            <td class="text-right">Anticipo:</td>
            <td class="text-right">-${{ number_format($pedido->anticipo, 2) }}</td>
        </tr>
        
        @php
            // Calculamos lo que se pagó al final (si ya está entregado, es el resto)
            $liquidacion = $pedido->total - $pedido->anticipo;
        @endphp

        @if($liquidacion > 0)
        <tr>
            <td class="text-right">Liquidación:</td>
            <td class="text-right bold">-${{ number_format($liquidacion, 2) }}</td>
        </tr>
        @endif

        <tr>
            <td class="text-right bold" style="padding-top: 5px;">Saldo Pendiente:</td>
            <td class="text-right bold" style="padding-top: 5px;">$0.00</td>
        </tr>
    </table>

    <div class="line"></div>
    
    <div class="text-center">
        <p>¡Gracias por su preferencia!</p>
        <p>Vuelva pronto.</p>
        <br>
        <small>*** Copia Cliente ***</small>
    </div>

</body>
</html>