<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Venta; 
use App\Models\MovimientoCaja; 
use App\Models\Anticipo; // Modelo correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Importamos PDF para que no falle

class CajaController extends Controller
{
    /**
     * Muestra el estado actual de la caja.
     */
    public function index()
    {
        // 1. Buscamos caja abierta
        $caja = Caja::where('user_id', Auth::id())
                ->where('estado', 'abierta')
                ->first();

        if (!$caja) {
            return view('cajas.index', ['cajaAbierta' => null]); 
        }

        // 2. Traemos las VENTAS
        $ventas = Venta::where('created_at', '>=', $caja->fecha_hora_apertura)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 3. Traemos los GASTOS
        $gastos = MovimientoCaja::where('caja_id', $caja->id)
                            ->where('tipo', 'egreso')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // 4. Traemos los ANTICIPOS
        $anticipos = Anticipo::where('created_at', '>=', $caja->fecha_hora_apertura)
                            ->orderBy('created_at', 'desc')
                            ->get();

        // 5. Cálculos
        $totalVentasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        
        // Sumar columna 'monto' solo si es efectivo
        $totalAnticiposEfectivo = $anticipos->filter(function ($anticipo) {
            return strtolower($anticipo->metodo_pago) === 'efectivo';
        })->sum('monto'); 
        
        $totalGastos = $gastos->sum('monto'); 

        // Saldo = Inicial + Ventas + Anticipos - Gastos
        $saldoActual = $caja->saldo_inicial + $totalVentasEfectivo + $totalAnticiposEfectivo - $totalGastos;

       return view('cajas.index', [
            'cajaAbierta' => $caja,  
            'ventas' => $ventas,
            'gastos' => $gastos,
            'anticipos' => $anticipos,
            'saldoActual' => $saldoActual,
            'totalGastos' => $totalGastos,
            'ventasEfectivo' => $totalVentasEfectivo,
            'anticiposEfectivo' => $totalAnticiposEfectivo 
        ]);
    }

    /**
     * Abre una nueva caja.
     */
    public function abrirCaja(Request $request)
    {
        $request->validate([
            'saldo_inicial' => 'required|numeric|min:0',
        ]);

        if (Caja::where('user_id', Auth::id())->where('estado', 'abierta')->exists()) {
            return redirect()->route('cajas.index')->with('error', 'Ya tienes una caja abierta.');
        }

        Caja::create([
            'user_id' => Auth::id(),
            'fecha_hora_apertura' => now(),
            'saldo_inicial' => $request->saldo_inicial,
            'estado' => 'abierta',
        ]);

        return redirect()->route('cajas.index')->with('success', 'Caja abierta exitosamente.');
    }

    /**
     * Cierra la caja abierta.
     */
    public function cerrarCaja(Request $request)
    {
        $caja = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return redirect()->route('cajas.index')->with('error', 'No se encontró ninguna caja abierta.');
        }

        DB::beginTransaction();
        
        try {
            // 1. Movimientos manuales
            $ingresosManuales = MovimientoCaja::where('caja_id', $caja->id)->where('tipo', 'ingreso')->sum('monto');
            $egresosManuales = MovimientoCaja::where('caja_id', $caja->id)->where('tipo', '!=', 'ingreso')->sum('monto');
            $saldoMovimientos = $ingresosManuales - $egresosManuales;
        
            // 2. Ventas Efectivo
            $ventasEfectivo = Venta::where('user_id', Auth::id()) 
                ->where('metodo_pago', 'efectivo')
                ->where('fecha_hora', '>=', $caja->fecha_hora_apertura) 
                ->where('fecha_hora', '<=', now()) 
                ->sum('total');

            // 3. Anticipos en Efectivo
            $anticiposQuery = Anticipo::where('user_id', Auth::id()) 
                ->where('created_at', '>=', $caja->fecha_hora_apertura)
                ->where('created_at', '<=', now())
                ->get(); 

            $anticiposEfectivo = $anticiposQuery->filter(function ($ant) {
                return strtolower($ant->metodo_pago) === 'efectivo';
            })->sum('monto');

            // 4. Saldo FINAL
            $saldoCalculadoCierre = $caja->saldo_inicial + $saldoMovimientos + $ventasEfectivo + $anticiposEfectivo;

            // Registrar Movimiento de VENTAS
            if ($ventasEfectivo > 0) {
                MovimientoCaja::create([
                    'caja_id' => $caja->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'descripcion' => 'Ventas en Efectivo del Turno',
                    'monto' => $ventasEfectivo,
                    'metodo_pago' => 'sistema',
                ]);
            }

            // 5. Registrar Movimiento de ANTICIPOS
            if ($anticiposEfectivo > 0) {
                MovimientoCaja::create([
                    'caja_id' => $caja->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'descripcion' => 'Anticipos/Apartados del Turno',
                    'monto' => $anticiposEfectivo,
                    'metodo_pago' => 'sistema',
                ]);
            }

            $caja->update([
                'fecha_hora_cierre' => now(),
                'saldo_final' => $saldoCalculadoCierre, 
                'estado' => 'cerrada',
            ]);

            DB::commit();
            return redirect()->route('cajas.index')->with('success', 'Caja cerrada. Saldo final: $' . number_format($saldoCalculadoCierre, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error al cerrar caja: " . $e->getMessage());
            return redirect()->route('cajas.index')->with('error', 'Error al cerrar la caja: ' . $e->getMessage());
        }
    }

    /**
     * Exporta las ventas, gastos y ANTICIPOS a CSV (Excel).
     */
    public function exportarVentasTurno()
    {
        $cajaAbierta = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'No hay ninguna caja abierta para exportar.');
        }

        // VENTAS
        $ventas = Venta::with('detalles.producto', 'user')
            ->where('user_id', Auth::id())
            ->where('fecha_hora', '>=', $cajaAbierta->fecha_hora_apertura)
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // GASTOS
        $gastos = MovimientoCaja::where('caja_id', $cajaAbierta->id)
            ->where('tipo', 'egreso')
            ->orderBy('created_at', 'asc')
            ->get();

        // ANTICIPOS
        $anticipos = Anticipo::where('created_at', '>=', $cajaAbierta->fecha_hora_apertura)
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = "Reporte_Caja_{$cajaAbierta->id}_{$cajaAbierta->fecha_hora_apertura->format('Y-m-d')}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function() use ($ventas, $gastos, $anticipos) {
            
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); 

            // === SECCIÓN 1: VENTAS ===
            fputcsv($file, ['REPORTE DE VENTAS']);
            fputcsv($file, ['ID Venta', 'Fecha', 'Método Pago', 'Total']);

            $totalVentas = 0;
            foreach ($ventas as $venta) {
                fputcsv($file, [
                    $venta->id,
                    \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m/Y'),
                    ucfirst($venta->metodo_pago),
                    $venta->total
                ]);
                if(strtolower($venta->metodo_pago) == 'efectivo') {
                    $totalVentas += $venta->total;
                }
            }
            fputcsv($file, []); 

            // === SECCIÓN 2: ANTICIPOS ===
            fputcsv($file, ['REPORTE DE ANTICIPOS / APARTADOS']);
            fputcsv($file, ['Pedido Ref', 'Fecha', 'Método Pago', 'Monto']);

            $totalAnticipos = 0;
            foreach ($anticipos as $anticipo) {
                fputcsv($file, [
                    'Pedido #' . $anticipo->pedido_id,
                    $anticipo->created_at->format('d/m/Y'),
                    ucfirst($anticipo->metodo_pago),
                    $anticipo->monto
                ]);
                if(strtolower($anticipo->metodo_pago) == 'efectivo') {
                    $totalAnticipos += $anticipo->monto;
                }
            }
            fputcsv($file, []);

            // === SECCIÓN 3: GASTOS ===
            fputcsv($file, ['REPORTE DE GASTOS']);
            fputcsv($file, ['Fecha', 'Descripción', 'Monto']);

            $totalGastos = 0;
            foreach ($gastos as $gasto) {
                fputcsv($file, [
                    $gasto->created_at->format('d/m/Y'),
                    $gasto->descripcion,
                    '-' . $gasto->monto
                ]);
                $totalGastos += $gasto->monto;
            }

            fputcsv($file, []);
            fputcsv($file, ['RESUMEN FINAL (EFECTIVO)']);
            fputcsv($file, ['Total Ventas:', $totalVentas]);
            fputcsv($file, ['Total Anticipos:', $totalAnticipos]);
            fputcsv($file, ['Total Gastos:', '-' . $totalGastos]);
            fputcsv($file, ['BALANCE FINAL:', $totalVentas + $totalAnticipos - $totalGastos]);
            
            fclose($file);
        }, 200, $headers);
    }

    /**
     * Genera el PDF del turno.
     */
    public function exportarVentasTurnoPDF()
    {
        $cajaAbierta = Caja::where('user_id', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'No hay caja abierta.');
        }

        $ventas = Venta::with('detalles.producto', 'user')
            ->where('user_id', Auth::id())
            ->where('fecha_hora', '>=', $cajaAbierta->fecha_hora_apertura)
            ->get();
        
        $gastos = MovimientoCaja::where('caja_id', $cajaAbierta->id)
            ->where('tipo', 'egreso')
            ->get();

        $anticipos = Anticipo::where('created_at', '>=', $cajaAbierta->fecha_hora_apertura)
            ->get();

        $ventasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        
        $anticiposEfectivo = $anticipos->filter(function ($ant) {
            return strtolower($ant->metodo_pago) === 'efectivo';
        })->sum('monto');

        $totalGastos = $gastos->sum('monto');
        $saldoActual = $cajaAbierta->saldo_inicial + $ventasEfectivo + $anticiposEfectivo - $totalGastos;

        $data = [
            'cajaAbierta' => $cajaAbierta,
            'ventas' => $ventas,
            'gastos' => $gastos,
            'anticipos' => $anticipos,
            'ventasEfectivo' => $ventasEfectivo,
            'anticiposEfectivo' => $anticiposEfectivo,
            'totalGastos' => $totalGastos,
            'saldoActual' => $saldoActual
        ];

        $pdf = Pdf::loadView('cajas.reporte_ventas_pdf', $data)
                  ->setPaper('a4', 'portrait');

        return $pdf->stream("Reporte_Caja_{$cajaAbierta->id}.pdf");
    }

} // <--- ¡AQUÍ TERMINA LA CLASE! Todo debe estar antes de esta llave.