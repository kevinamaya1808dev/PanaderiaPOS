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
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-success { color: green; }
        .text-danger { color: red; }
        .summary-table { width: 50%; float: right; }
        .summary-table td { border: none; padding: 4px; }
        .productos-cell { font-size: 11px; line-height: 1.4; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Panadería Kairos</h1>
        <p>Reporte de Ventas del Turno</p>
        <p><strong>Cajero:</strong> {{ $cajaAbierta->user->name ?? 'N/A' }}</p>
        <p><strong>Fecha de Apertura:</strong> {{ $cajaAbierta->fecha_hora_apertura->format('d/m/Y H:i') }}</p>
    </div>

    {{-- 1. TABLA DE VENTAS --}}
    <h3>Desglose de Ventas del Turno</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 15%;">Fecha</th>
                <th>Productos</th>
                <th style="width: 15%;">Total</th>
                <th style="width: 15%;">Método</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td>{{ $venta->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y') }}</td>
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
                    <td colspan="5" style="text-align: center;">No se registraron ventas en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 2. TABLA DE ANTICIPOS (NUEVA SECCIÓN) --}}
    <h3>Anticipos / Apartados</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Fecha</th>
                <th>Referencia / Pedido</th>
                <th style="width: 15%;">Método</th>
                <th style="width: 15%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anticipos as $anticipo)
                <tr>
                    <td>{{ $anticipo->created_at->format('d/m/Y') }}</td>
                    <td>Pedido #{{ $anticipo->pedido_id }}</td>
                    <td>{{ ucfirst($anticipo->metodo_pago) }}</td>
                    <td class="text-right text-success">+${{ number_format($anticipo->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No hay anticipos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 3. TABLA DE GASTOS --}}
    <h3>Gastos y Salidas</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Fecha</th>
                <th>Descripción</th>
                <th style="width: 15%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gastos as $gasto)
                <tr>
                    <td>{{ $gasto->created_at->format('d/m/Y') }}</td>
                    <td>{{ $gasto->descripcion }}</td>
                    <td class="text-right text-danger">-${{ number_format($gasto->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No hay gastos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>

    {{-- 4. RESUMEN FINANCIERO --}}
    <h3>Resumen Financiero (Efectivo)</h3>
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
            <td>(+) Anticipos en Efectivo:</td>
            <td class="text-right">${{ number_format($anticiposEfectivo, 2) }}</td>
        </tr>
        <tr>
            <td>(-) Gastos / Salidas:</td>
            <td class="text-right text-danger">-${{ number_format($totalGastos, 2) }}</td>
        </tr>
        <tr>
            <td class="text-bold" style="border-top: 1px solid #000; padding-top: 5px;">Saldo Final Calculado:</td>
            <td class="text-right text-bold" style="border-top: 1px solid #000; padding-top: 5px;">
                ${{ number_format($saldoActual, 2) }}
            </td>
        </tr>
    </table>

</body>
</html>