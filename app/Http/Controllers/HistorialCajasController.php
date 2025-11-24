<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Venta;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;

class HistorialCajasController extends Controller
{
    /**
     * Muestra el listado de todas las cajas.
     */
    public function index()
    {
        $cajas = Caja::with('user')
                    ->orderBy('fecha_hora_apertura', 'desc')
                    ->paginate(10); 

        return view('historial_cajas.index', compact('cajas'));
    }

    /**
     * Muestra el detalle completo (Balance + Ventas + Movimientos).
     */
    public function show(Caja $caja)
    {
        // 1. Obtenemos las ventas CON sus productos para poder listarlas
        $ventas = Venta::with('detalles.producto', 'user') // <-- Carga ansiosa importante
                        ->where('user_id', $caja->user_id)
                        ->where('fecha_hora', '>=', $caja->fecha_hora_apertura)
                        ->where('fecha_hora', '<=', $caja->fecha_hora_cierre ?? now())
                        ->orderBy('fecha_hora', 'desc') // Las más recientes primero
                        ->get();

        $totalVentasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        
        // 2. Obtenemos movimientos
        $movimientos = MovimientoCaja::where('caja_id', $caja->id)->orderBy('created_at', 'desc')->get();
        
        $ingresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $egresos = $movimientos->where('tipo', '!=', 'ingreso')->sum('monto');
        
        $hora = $caja->fecha_hora_apertura->hour;
        $nombreTurno = ($hora < 14) ? 'Matutino (AM)' : 'Vespertino (PM)';

        // Pasamos 'ventas' a la vista
        return view('historial_cajas.show', compact('caja', 'totalVentasEfectivo', 'ingresos', 'egresos', 'nombreTurno', 'movimientos', 'ventas'));
    }
}