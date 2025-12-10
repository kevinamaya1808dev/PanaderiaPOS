<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CobrarVentaController extends Controller
{
    /**
     * Muestra la página principal para cobrar
     */
    public function index()
    {
        $cajaAbierta = Caja::where('user_id', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'Debes abrir tu caja antes de poder cobrar ventas.');
        }

        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto', 'cliente') 
                            ->where('status', 'Pendiente')
                            ->orderBy('fecha_hora', 'asc')
                            ->get();

        return view('cobrar.index', compact('ventasPendientes'));
    }

    /**
     * Devuelve la lista de pendientes en JSON para el refresco automático.
     */
    public function getVentasPendientes()
    {
        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto', 'cliente') 
                            ->where('status', 'Pendiente')
                            ->orderBy('fecha_hora', 'asc')
                            ->get();
        
        return response()->json($ventasPendientes);
    }


    /**
     * Busca una venta pendiente por su ID (Folio).
     */
    public function buscar(Request $request)
    {
        $request->validate(['folio' => 'required|integer']);
        $folio = $request->input('folio');

        $venta = Venta::withoutGlobalScopes() 
                    ->with('detalles.producto', 'user', 'cliente') 
                    ->where('id', $folio)
                    ->where('status', 'Pendiente') 
                    ->first();

        if ($venta) {
            return response()->json($venta);
        } else {
            return response()->json(['error' => 'Venta no encontrada, ya fue pagada o no existe.'], 404);
        }
    }

    /**
     * Marca una venta pendiente como 'Pagada'.
     * AQUI ESTÁN LOS CAMBIOS PARA GUARDAR LA REFERENCIA
     */
    public function pagar(Request $request) 
    {
        $request->validate([
            'venta_id' => 'required|integer|exists:ventas,id', 
            'metodo_pago' => 'required|string|in:efectivo,tarjeta',
            'monto_recibido' => 'required|numeric|min:0',
            'monto_entregado' => 'required|numeric|min:0',
            // 1. VALIDAMOS QUE EL CAMPO REFERENCIA SEA TEXTO (OPCIONAL)
            'referencia_pago' => 'nullable|string|max:100', // <--- AGREGAR ESTO
        ]);

        $venta = Venta::withoutGlobalScopes()->find($request->venta_id);

        if (!$venta) {
             return response()->json(['error' => 'La venta no existe.'], 404);
        }

        if ($venta->status != 'Pendiente') {
            return response()->json(['error' => 'Esta venta ya fue pagada.'], 400);
        }

        DB::beginTransaction();
        try {
            $venta->status = 'Pagada';
            $venta->metodo_pago = $request->metodo_pago;
            
            // 2. ASIGNAMOS LA REFERENCIA AL OBJETO VENTA
            // Si es efectivo vendrá null, si es tarjeta vendrá el texto
            $venta->referencia_pago = $request->referencia_pago; // <--- AGREGAR ESTO
            
            $venta->monto_recibido = $request->monto_recibido;
            $venta->monto_entregado = $request->monto_entregado;
            $venta->user_id = Auth::id(); // Guardamos quién cobró realmente
            $venta->save();
            
            DB::commit();

            return response()->json([
                'message' => '¡Venta cobrada exitosamente!',
                'venta_id' => $venta->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al cobrar venta pendiente {$venta->id}: " . $e->getMessage());
            return response()->json(['error' => 'Error interno al guardar el pago.'], 500);
        }
    }
}