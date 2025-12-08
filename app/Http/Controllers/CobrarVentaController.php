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

        // --- CORRECCIÓN AQUÍ: Agregado 'cliente' al with() ---
        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto', 'cliente') // <--- AQUÍ FALTABA EL CLIENTE
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
        // --- CORRECCIÓN AQUÍ: Agregado 'cliente' al with() ---
        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto', 'cliente') // <--- AQUÍ TAMBIÉN
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

        // --- CORRECCIÓN AQUÍ: Agregado 'cliente' al with() ---
        $venta = Venta::withoutGlobalScopes() 
                    ->with('detalles.producto', 'user', 'cliente') // <--- Y AQUÍ (CRUCIAL PARA LA BÚSQUEDA)
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
     * (Sin cambios en lógica)
     */
    public function pagar(Request $request) 
    {
        $request->validate([
            'venta_id' => 'required|integer|exists:ventas,id', 
            'metodo_pago' => 'required|string|in:efectivo,tarjeta',
            'monto_recibido' => 'required|numeric|min:0',
            'monto_entregado' => 'required|numeric|min:0',
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
            $venta->monto_recibido = $request->monto_recibido;
            $venta->monto_entregado = $request->monto_entregado;
            $venta->user_id = Auth::id(); 
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