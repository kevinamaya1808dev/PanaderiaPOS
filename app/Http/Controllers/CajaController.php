<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Venta; 
use App\Models\MovimientoCaja; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Muestra el estado actual de la caja.
     */
    public function index()
    {
        // 1. Buscamos si el usuario tiene una CAJA ABIERTA
        $caja = Caja::where('user_id', Auth::id())
                ->where('estado', 'abierta')
                ->first();

        if (!$caja) {
            // Reutilizamos el index, pero le decimos que NO hay caja abierta
            return view('cajas.index', ['cajaAbierta' => null]); 
        }

        // 2. Traemos las VENTAS por FECHA (ya que no tienes caja_id)
        // Buscamos ventas hechas DESPUÉS de la hora de apertura
        $ventas = Venta::where('created_at', '>=', $caja->fecha_hora_apertura)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 3. Traemos los GASTOS de la misma forma (por fecha/hora)
        $gastos = MovimientoCaja::where('caja_id', $caja->id)
                            ->where('tipo', 'egreso')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // 4. Cálculos
        $totalVentasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $totalGastos = $gastos->sum('monto'); 

        // Saldo = Inicial + Ventas - Gastos
        $saldoActual = $caja->saldo_inicial + $totalVentasEfectivo - $totalGastos;

       return view('cajas.index', [
            'cajaAbierta' => $caja,  
            'ventas' => $ventas,
            'gastos' => $gastos,
            'saldoActual' => $saldoActual,
            'totalGastos' => $totalGastos,
            'ventasEfectivo' => $totalVentasEfectivo 
        ]);
    }

    /**
     * Abre una nueva caja (Sin cambios necesarios aquí).
     */
    public function abrirCaja(Request $request)
    {
        $request->validate([
            'saldo_inicial' => 'required|numeric|min:0',
        ]);

        if (Caja::where('user_id', Auth::id())->where('estado', 'abierta')->exists()) {
            return redirect()->route('cajas.index')->with('error', 'Ya tienes una caja abierta. Cierra el turno anterior primero.');
        }

        Caja::create([
            'user_id' => Auth::id(),
            'fecha_hora_apertura' => now(),
            'saldo_inicial' => $request->saldo_inicial,
            'estado' => 'abierta',
        ]);

        return redirect()->route('cajas.index')->with('success', 'Caja abierta exitosamente con saldo inicial de $' . number_format($request->saldo_inicial, 2));
    }

    /**
     * Cierra la caja abierta (Modificado para incluir ventas y crear movimiento).
     */
    public function cerrarCaja(Request $request)
    {
        $caja = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return redirect()->route('cajas.index')->with('error', 'No se encontró ninguna caja abierta para cerrar.');
        }

        // <-- MODIFICADO: Usar transacción para asegurar atomicidad -->
        DB::beginTransaction();
        
        try {
            // Calcular saldo de movimientos manuales
            // 1. Sumar todos los ingresos
            $ingresosManuales = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo', 'ingreso')
                ->sum('monto');

            // 2. Sumar todos los egresos (todo lo que NO sea 'ingreso')
            $egresosManuales = MovimientoCaja::where('caja_id', $caja->id)
                ->where('tipo', '!=', 'ingreso')
                ->sum('monto');

            // 3. Calcular el saldo final de movimientos
            $saldoMovimientos = $ingresosManuales - $egresosManuales;
        
            // <-- TODO EL CÓDIGO SIGUIENTE AHORA VA DENTRO DEL 'try' -->

            // <-- MODIFICADO: Calcular Ventas en Efectivo para ESTA caja específica -->
            $ventasEfectivo = Venta::where('user_id', Auth::id()) 
                ->where('metodo_pago', 'efectivo')
                ->where('fecha_hora', '>=', $caja->fecha_hora_apertura) // Desde apertura
                ->where('fecha_hora', '<=', now()) // Hasta el cierre
                // ->where('caja_id', $caja->id) // Si tuvieras caja_id en Ventas
                ->sum('total');

            // <-- MODIFICADO: Calcular saldo final incluyendo ventas en efectivo -->
            $saldoCalculadoCierre = $caja->saldo_inicial + $saldoMovimientos + $ventasEfectivo;

            // <-- NUEVO: Registrar las ventas en efectivo como un movimiento de caja al cerrar -->
            if ($ventasEfectivo > 0) {
                MovimientoCaja::create([
                    'caja_id' => $caja->id,
                    'user_id' => Auth::id(), // <-- AÑADIDO: Guardar quién hizo el movimiento
                    'tipo' => 'ingreso',
                    'descripcion' => 'Ventas en Efectivo del Turno',
                    'monto' => $ventasEfectivo,
                    'metodo_pago' => 'sistema', // Indicar que es automático
                    'created_at' => now(), // Asegurar timestamp
                    'updated_at' => now(),
                ]);
            }

            // Actualizar el registro de caja
            $caja->update([
                'fecha_hora_cierre' => now(),
                'saldo_final' => $saldoCalculadoCierre, // Usar el saldo calculado que incluye ventas
                'estado' => 'cerrada',
            ]);

            DB::commit(); // Confirmar transacción

            return redirect()->route('cajas.index')->with('success', 'Caja cerrada exitosamente. Saldo final calculado: $' . number_format($saldoCalculadoCierre, 2));

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en caso de error
            \Log::error("Error al cerrar caja: " . $e->getMessage()); // Loguear el error
            return redirect()->route('cajas.index')->with('error', 'Ocurrió un error al cerrar la caja. Verifique los logs.');
        }
    }

    // ===================================================================
    // ============= ¡NUEVA FUNCIÓN PARA MOVIMIENTOS MANUALES! =============
    // ===================================================================
    /**
     * Registra un movimiento manual (ingreso o egreso) en la caja abierta.
     */
    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'tipo' => 'required|in:ingreso,egreso', // Validar 'ingreso' o 'egreso'
            'monto' => 'required|numeric|min:0.01', // Monto siempre positivo
            'descripcion' => 'required|string|max:255',
        ]);

        // Doble chequeo: la caja debe estar abierta y pertenecer al usuario
        $cajaAbierta = Caja::where('id', $request->caja_id)
                           ->where('user_id', Auth::id())
                           ->where('estado', 'abierta')
                           ->first();
                            
        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'Error: No se encontró tu caja abierta.');
        }

        // Guardamos el movimiento usando tu estructura de tabla
        MovimientoCaja::create([
            'caja_id' => $cajaAbierta->id,
            'user_id' => Auth::id(), // Guardar quién lo hizo
            'tipo' => $request->tipo, // 'ingreso' o 'egreso'
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'metodo_pago' => 'Efectivo (Manual)', // Método de pago descriptivo
        ]);
        
        return redirect()->route('cajas.index')->with('success', 'Movimiento manual registrado exitosamente.');
    }

    /**
     * Exporta las ventas y gastos del turno actual a un archivo CSV.
     * (MODIFICADO: INCLUYE GASTOS Y RESUMEN, SOLO FECHA d/m/Y)
     */
    public function exportarVentasTurno()
    {
        // 1. Encontrar la caja abierta
        $cajaAbierta = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'No hay ninguna caja abierta para exportar.');
        }

        // 2. Obtener VENTAS
        $ventas = Venta::with('detalles.producto', 'user')
            ->where('user_id', Auth::id())
            ->where('fecha_hora', '>=', $cajaAbierta->fecha_hora_apertura)
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // 3. Obtener GASTOS (Movimientos de tipo 'egreso')
        $gastos = MovimientoCaja::where('caja_id', $cajaAbierta->id)
            ->where('tipo', 'egreso')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($ventas->isEmpty() && $gastos->isEmpty()) {
            return redirect()->route('cajas.index')->with('error', 'No hay registros para exportar en este turno.');
        }

        // 4. Definir nombre del archivo
        $fileName = "Reporte_Caja_Completo_{$cajaAbierta->id}_{$cajaAbierta->fecha_hora_apertura->format('Y-m-d')}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // 5. Crear la respuesta
        return response()->stream(function() use ($ventas, $gastos) {
            
            $file = fopen('php://output', 'w');
            
            // BOM para acentos
            fputs($file, "\xEF\xBB\xBF");

            // ==========================================
            // SECCIÓN 1: VENTAS
            // ==========================================
            fputcsv($file, ['REPORTE DE VENTAS']);
            fputcsv($file, [
                'ID Venta', 
                'Fecha', // Título "Fecha"
                'Cajero', 
                'Método Pago',
                'Productos', 
                'Total Venta'          
            ]);

            $totalVentas = 0;

            foreach ($ventas as $venta) {
                $fecha = \Carbon\Carbon::parse($venta->fecha_hora);
                
                // Procesar productos
                $productosArray = [];
                foreach ($venta->detalles as $detalle) {
                    $nombreProducto = $detalle->producto->nombre ?? 'N/A';
                    $productosArray[] = "{$detalle->cantidad} x {$nombreProducto}";
                }
                $productosString = implode(" | ", $productosArray);

                fputcsv($file, [
                    $venta->id,
                    $fecha->format('d/m/Y'), // <--- SOLO FECHA
                    $venta->user->name ?? 'N/A',
                    ucfirst($venta->metodo_pago),
                    $productosString,    
                    $venta->total          
                ]);

                if($venta->metodo_pago == 'efectivo') {
                    $totalVentas += $venta->total;
                }
            }

            // ==========================================
            // SEPARADOR
            // ==========================================
            fputcsv($file, []); 
            fputcsv($file, []); 

            // ==========================================
            // SECCIÓN 2: GASTOS
            // ==========================================
            fputcsv($file, ['REPORTE DE GASTOS / SALIDAS']);
            fputcsv($file, [
                'Fecha', // Título "Fecha"
                'Descripción del Gasto', 
                'Responsable',
                '', 
                '', 
                'Monto Retirado'          
            ]);

            $totalGastos = 0;

            foreach ($gastos as $gasto) {
                fputcsv($file, [
                    $gasto->created_at->format('d/m/Y'), // <--- SOLO FECHA
                    $gasto->descripcion,
                    $gasto->user->name ?? 'Sistema',
                    '', 
                    '', 
                    '-' . $gasto->monto 
                ]);
                $totalGastos += $gasto->monto;
            }

            // ==========================================
            // SEPARADOR Y RESUMEN FINAL
            // ==========================================
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['RESUMEN DEL TURNO']);
            
            fputcsv($file, ['', '', '', '', 'Total Ventas Efectivo:', $totalVentas]);
            fputcsv($file, ['', '', '', '', 'Total Gastos:', '-' . $totalGastos]);
            fputcsv($file, ['', '', '', '', 'BALANCE FINAL:', $totalVentas - $totalGastos]);
            
            fclose($file);
        }, 200, $headers);
    }

    public function exportarVentasTurnoPDF()
    {
        // 1. Obtener la caja abierta (igual que en exportarVentasTurno)
        $cajaAbierta = Caja::where('user_id', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'No hay ninguna caja abierta para exportar.');
        }

        // 2. Necesitamos RECOGER TODOS LOS DATOS que la vista `index` calcula,
        //    porque el PDF también necesita los resúmenes.

        // Ventas (con detalles)
        $ventas = Venta::with('detalles.producto', 'user')
            ->where('user_id', Auth::id())
            ->where('fecha_hora', '>=', $cajaAbierta->fecha_hora_apertura)
            ->orderBy('fecha_hora', 'asc')
            ->get();
        
        // Movimientos
        $movimientos = MovimientoCaja::where('caja_id', $cajaAbierta->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Calcular Resúmenes (igual que en la función index)
        
        // Ventas en Efectivo
        $ventasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');

        // Saldo Movimientos Manuales
        $ingresosManuales = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresosManuales = $movimientos->where('tipo', '!=', 'ingreso')->sum('monto');
        $saldoMovimientos = $ingresosManuales - $egresosManuales;

        // Saldo Actual Total
        $saldoActual = $cajaAbierta->saldo_inicial + $ventasEfectivo + $saldoMovimientos;

        // 4. Preparar los datos para la vista
        $data = [
            'cajaAbierta' => $cajaAbierta,
            'ventas' => $ventas,
            'movimientos' => $movimientos,
            'ventasEfectivo' => $ventasEfectivo,
            'saldoMovimientos' => $saldoMovimientos,
            'saldoActual' => $saldoActual
        ];

        // 5. Generar y enviar el PDF
        // Usamos la NUEVA vista 'reporte_ventas_pdf'
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cajas.reporte_ventas_pdf', $data)
                                        ->setPaper('a4', 'portrait'); // 'portrait' (vertical) o 'landscape' (horizontal)

        // Descargar (o mostrar en navegador)
        $fileName = "Reporte_Ventas_Caja_{$cajaAbierta->id}_{$cajaAbierta->fecha_hora_apertura->format('Y-m-d')}.pdf";
        return $pdf->stream($fileName); // stream() lo muestra, download() lo descarga
    }
}