<?php

namespace App\Http\Controllers;

use App\Models\Caja;
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
        // Obtener la caja abierta por el usuario actual
        $cajaAbierta = Caja::with('user')
            ->where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        $movimientos = collect();
        $saldoActual = 0;

        if ($cajaAbierta) {
            // Obtener sus movimientos manuales
            $movimientos = MovimientoCaja::where('caja_id', $cajaAbierta->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calcular el saldo actual (Saldo Inicial + Movimientos)
            $saldoMovimientos = $movimientos->sum(function ($mov) {
                return $mov->tipo === 'ingreso' ? $mov->monto : -$mov->monto;
            });
            
            // Aquí se sumarían las ventas en efectivo si ya existieran
            // $ventasEfectivo = Venta::where('caja_id', $cajaAbierta->id)->where('metodo_pago', 'efectivo')->sum('total');
            
            $saldoActual = $cajaAbierta->saldo_inicial + $saldoMovimientos; // + $ventasEfectivo;
        }

        return view('cajas.index', compact('cajaAbierta', 'movimientos', 'saldoActual'));
    }

    /**
     * Abre una nueva caja.
     */
    public function abrirCaja(Request $request)
    {
        // El middleware 'permiso:cajas,alta' protege esta función
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
     * Cierra la caja abierta.
     */
    public function cerrarCaja(Request $request)
    {
        // El middleware 'permiso:cajas,eliminar' protege esta función
        $caja = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$caja) {
            return redirect()->route('cajas.index')->with('error', 'No se encontró ninguna caja abierta para cerrar.');
        }

        // Calcular saldo final basado en movimientos (y ventas futuras)
        $saldoMovimientos = MovimientoCaja::where('caja_id', $caja->id)->sum(function ($mov) {
            return $mov->tipo === 'ingreso' ? $mov->monto : -$mov->monto;
        });
        
        // Aquí se sumarían las ventas en efectivo
        // $ventasEfectivo = Venta::where('caja_id', $caja->id)->where('metodo_pago', 'efectivo')->sum('total');

        $saldoCalculado = $caja->saldo_inicial + $saldoMovimientos; // + $ventasEfectivo;

        // Actualizar el registro de caja
        $caja->update([
            'fecha_hora_cierre' => now(),
            'saldo_final' => $saldoCalculado,
            'estado' => 'cerrada',
        ]);

        return redirect()->route('cajas.index')->with('success', 'Caja cerrada exitosamente. Saldo final registrado: $' . number_format($saldoCalculado, 2));
    }
}