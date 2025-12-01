<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\MovimientoCaja; 
use App\Models\Caja;
use App\Models\Anticipo; // <--- NUEVO MODELO
// Eliminamos Venta y DetalleVenta para no mezclar peras con manzanas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    // Mostrar lista de pedidos (Agenda)
    public function index()
    {
        $pedidos = Pedido::with('cliente')
            ->where('estatus', '!=', 'entregado') 
            ->orderBy('fecha_entrega', 'asc')
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    // Mostrar formulario de nuevo pedido
    public function create()
    {
        $clientes = Cliente::all();
        $productos = Producto::all();
        return view('pedidos.create', compact('clientes', 'productos'));
    }

    // Guardar el pedido
    public function store(Request $request)
    {
        $request->validate([
            'nombre_cliente' => 'required|string',
            'fecha_entrega' => 'required|date|after:today',
            'anticipo' => 'required|numeric|min:0',
            'productos' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            // 0. OBTENER CAJA ACTUAL
            $cajaAbierta = null;
            if ($request->anticipo > 0) {
                $cajaAbierta = Caja::latest()->first();

                if (!$cajaAbierta) {
                    throw new \Exception('¡No se encontró ninguna caja registrada en el sistema! Abre turno primero.');
                }
            }

            // 1. Calcular el Total Real
            $totalCalculado = 0;
            $productosData = $request->productos;
            
            foreach ($productosData as $prod) {
                $totalCalculado += $prod['precio'] * $prod['cantidad'];
            }

            // 2. Crear el Pedido (La "Orden")
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id, 
                'nombre_cliente' => $request->nombre_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'fecha_entrega' => $request->fecha_entrega,
                'total' => $totalCalculado,
                'anticipo' => $request->anticipo,
                'notas_especiales' => $request->notas_especiales,
                'estatus' => 'pendiente',
                'user_id' => Auth::id(),
            ]);

            // 3. Guardar Detalles del Pedido
            foreach ($productosData as $prod) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $prod['id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'especificaciones' => $prod['notas'] ?? null,
                ]);
            }

            // 4. LOGICA DE COBRO (ANTICIPO)
            if ($request->anticipo > 0 && $cajaAbierta) {
                 
                // A. Registrar el ANTICIPO (Registro administrativo)
                // Esto es mucho más limpio que crear una venta falsa
                Anticipo::create([
                    'pedido_id' => $pedido->id,
                    'caja_id' => $cajaAbierta->id,
                    'monto' => $request->anticipo,
                    'metodo_pago' => 'Efectivo', // Puedes hacerlo dinámico si agregas select en el form
                    'user_id' => Auth::id(),
                ]);

                // B. Registrar en MOVIMIENTOS DE CAJA (Dinero físico)
                // Esto asegura que el dinero sume en el "Total en Cajón"
                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id, 
                    'tipo' => 'ingreso',
                    'monto' => $request->anticipo,
                    'concepto' => 'Anticipo Pedido #' . $pedido->id . ' (' . $request->nombre_cliente . ')',
                    'user_id' => Auth::id(),
                    'fecha' => now(),
                    'metodo_pago' => 'Efectivo', 
                ]);
            }

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido registrado correctamente. Folio: ' . $pedido->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    // Método para marcar como entregado y cobrar saldo
    public function entregar($id)
    {
        $pedido = Pedido::findOrFail($id);
        $saldo = $pedido->saldo_pendiente;

        DB::beginTransaction();
        try {
            // 1. Cobrar saldo si existe
            if ($saldo > 0) {
                $cajaAbierta = Caja::latest()->first();

                if (!$cajaAbierta) {
                    throw new \Exception('¡No hay ninguna caja disponible para recibir el saldo!');
                }

                // A. Registrar el ANTICIPO FINAL (Liquidación)
                Anticipo::create([
                    'pedido_id' => $pedido->id,
                    'caja_id' => $cajaAbierta->id,
                    'monto' => $saldo,
                    'metodo_pago' => 'Efectivo',
                    'user_id' => Auth::id(),
                ]);

                // B. Registrar en CAJA (Dinero físico)
                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id,
                    'tipo' => 'ingreso',
                    'monto' => $saldo,
                    'concepto' => 'Liquidación Pedido #' . $pedido->id,
                    'user_id' => Auth::id(),
                    'fecha' => now(),
                    'metodo_pago' => 'Efectivo',
                ]);
            }

            // 2. Actualizar estatus
            $pedido->update([
                'estatus' => 'entregado',
                'anticipo' => $pedido->total 
            ]);
            
            DB::commit();
            return back()->with('success', 'Pedido entregado y saldo cobrado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}