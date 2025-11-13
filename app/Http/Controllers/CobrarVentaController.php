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
     * (¡AHORA TAMBIÉN CARGA LA LISTA INICIAL DE PENDIENTES!)
     */
    public function index()
    {
        $cajaAbierta = Caja::where('user_id', Auth::id())
                            ->where('estado', 'abierta')
                            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'Debes abrir tu caja antes de poder cobrar ventas.');
        }

        // --- ¡NUEVO! Obtenemos la lista de pendientes al cargar ---
        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto') // Cargar lo necesario
                            ->where('status', 'Pendiente')
                            ->orderBy('fecha_hora', 'asc') // Primero en entrar, primero en salir
                            ->get();
        // --- FIN NUEVO ---

        return view('cobrar.index', compact('ventasPendientes')); // <-- Pasamos la variable a la vista
    }

    /**
     * ¡NUEVA FUNCIÓN!
     * Devuelve la lista de pendientes en JSON para el refresco automático.
     */
    public function getVentasPendientes()
    {
        $ventasPendientes = Venta::withoutGlobalScopes()
                            ->with('user', 'detalles.producto')
                            ->where('status', 'Pendiente')
                            ->orderBy('fecha_hora', 'asc')
                            ->get();
        
        // Devolvemos la vista parcial (o JSON, pero la vista es más fácil de renderizar)
        // No, devolvemos JSON, es más limpio para el JS.
        return response()->json($ventasPendientes);
    }


    /**
     * Busca una venta pendiente por su ID (Folio).
     * (Sin cambios, pero ahora el JS la usará de forma diferente)
     */
    public function buscar(Request $request)
    {
        $request->validate(['folio' => 'required|integer']);
        $folio = $request->input('folio');

        $venta = Venta::withoutGlobalScopes() 
                    ->with('detalles.producto', 'user') 
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
     * (Sin cambios)
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