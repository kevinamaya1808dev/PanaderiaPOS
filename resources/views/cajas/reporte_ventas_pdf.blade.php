<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas del Turno</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; } /* vertical-align: top es útil aquí */
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .summary-table { width: 50%; float: right; }
        .summary-table td { border: none; padding: 4px; }
        /* Estilo para la celda de productos */
        .productos-cell { font-size: 11px; line-height: 1.4; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Panadería "Tu Nombre"</h1>
        <p>Reporte de Ventas del Turno</p>
        <p><strong>Cajero:</strong> {{ $cajaAbierta->user->name ?? 'N/A' }}</p>
        <p><strong>Fecha de Apertura:</strong> {{ $cajaAbierta->fecha_hora_apertura->format('d/m/Y') }}</p>
    </div>

    <h2>Desglose de Ventas del Turno</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID Venta</th>
                <th style="width: 15%;">Fecha</th>
                <th>Productos (Desglose)</th>
                <th style="width: 15%;">Total Venta</th>
                <th style="width: 15%;">Método Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
                {{-- Ahora solo hay UNA fila por VENTA --}}
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/y') }}</td>
                    
                    {{-- Celda con la lista de productos --}}
                    <td class="productos-cell">
                        @foreach($venta->detalles as $detalle)
                            {{ $detalle->cantidad }} x {{ $detalle->producto->nombre ?? 'N/A' }}<br>
                        @endforeach
                    </td>

                    <td class="text-right">${{ number_format($venta->total, 2) }}</td>
                    <td>{{ ucfirst($venta->metodo_pago) }}</td>
                </tr>
            @empty
                <tr>
                    {{-- El colspan ahora es 5 --}}
                    <td colspan="5">No se registraron ventas en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>

    <h2>Movimientos Manuales</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('d/m/y') }}</td>
                    <td>{{ ucfirst($mov->tipo) }}</td>
                    <td>{{ $mov->descripcion ?? 'N/A' }}</td>
                    <td class="text-right {{ $mov->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                        {{ $mov->tipo == 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No se registraron movimientos manuales.</td>
                </tr>
            {{-- ----- ¡AQUÍ ESTABA EL ERROR! ----- --}}
            {{-- Escribí @endforese en lugar de @endforelse --}}
            @endforelse
            {{-- ----- FIN DE LA CORRECCIÓN ----- --}}
        </tbody>
    </table>
    
    <br>

    <h2>Resumen Financiero del Turno</h2>
    <table class="summary-table">
        <tr>
            <td>Saldo Inicial:</td>
            <td class="text-right">${{ number_format($cajaAbierta->saldo_inicial, 2) }}</td>
        </tr>
        <tr>
            <td>(+) Ventas en Efectivo:</td>
            <td class="text-right">${{ number_format($ventasEfectivo, 2) }}</td>
        </tr>
        <tr>
            <td>(+/-) Movimientos Manuales:</td>
            <td class="text-right">{{ $saldoMovimientos >= 0 ? '+' : '-' }}${{ number_format(abs($saldoMovimientos), 2) }}</td>
        </tr>
        <tr>
            <td class="text-bold">Saldo Final Estimado:</td>
            <td class="text-right text-bold">${{ number_format($saldoActual, 2) }}</td>
        </tr>
    </table>

</body>
</html>