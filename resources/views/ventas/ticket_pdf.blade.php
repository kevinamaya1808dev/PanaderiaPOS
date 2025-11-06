<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Venta #{{ $venta->id }}</title>
    <link rel="stylesheet" href="{{ public_path('css/ticket.css') }}">
</head>
<body>
    <article class="ticket-body">
        
        <header class="text-center">
            <h4>Panaderia "JIREH"</h4>
            <p>Calle Allende, Chalco EDOMEX.</p>
            <p>Tel: 621382826</p>
        </header>

        <hr>

        <section>
            <p><strong>Folio Venta:</strong> #{{ $venta->id }}</p>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y h:i A') }}</p>
            <p><strong>Cajero:</strong> {{ $venta->user->name ?? 'N/A' }}</p>
            <p><strong>Cliente:</strong> {{ $venta->cliente->Nombre ?? 'Publico General' }}</p>
        </section>

        <hr>

        <section>
            <div>
                <span><strong>CANT PRODUCTO</strong></span>
                <span class="float-right"><strong>IMPORTE</strong></span>
            </div>

            @foreach($venta->detalles as $detalle)
                <div>
                    <span>{{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'N/A' }}</span>
                    <span class="float-right">${{ number_format($detalle->importe, 2) }}</span>
                </div>
            @endforeach
        </section>

        <hr>

        <section>
            <div>
                <span class="float-right"><strong>TOTAL:</strong> ${{ number_format($venta->total, 2) }}</span>
            </div>

            @if($venta->metodo_pago == 'efectivo')
                <div>
                    <span class="float-right">Recibido: ${{ number_format($venta->monto_recibido, 2) }}</span>
                </div>
                <div>
                    <span class="float-right">Cambio: ${{ number_format($venta->monto_entregado, 2) }}</span>
                </div>
            @endif
        </section>

        <hr>

        <footer class="text-center">
            <p>¡Gracias por su compra!</p>
            <p>¿Necesitas un Software para tu negocio?</p>
            <p>¡Contactanos!  www.ollintem.com.mx</p>
        </footer>

    </article>
</body>
</html>