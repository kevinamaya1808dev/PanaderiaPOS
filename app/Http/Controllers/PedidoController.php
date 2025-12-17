<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\MovimientoCaja; 
use App\Models\Caja;
use App\Models\Anticipo; 
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
            ->where('estatus', '!=', 'cancelado') 
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
    // Guardar el pedido (Imagen 4)
    public function store(Request $request)
    {
        // 1. Validar que recibimos el método y la referencia
        $request->validate([
            'nombre_cliente' => 'required|string',
            'fecha_entrega' => 'required|date|after:today',
            'anticipo' => 'required|numeric|min:0',
            'productos' => 'required|array|min:1',
            // ESTO ES VITAL: Validar los campos del modal
            'metodo_pago' => 'nullable|string', 
            'referencia_pago' => 'nullable|string|max:100', 
        ]);

        DB::beginTransaction();

        try {
            // 0. OBTENER CAJA ACTUAL
            $cajaAbierta = null;
            if ($request->anticipo > 0) {
                $cajaAbierta = Caja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->first();

                if (!$cajaAbierta) {
                    throw new \Exception('¡No tienes una caja abierta! Abre turno antes de recibir anticipos.');
                }
            }

            // 1. Calcular el Total Real
            $totalCalculado = 0;
            $productosData = $request->productos;
            
            foreach ($productosData as $prod) {
                $totalCalculado += $prod['precio'] * $prod['cantidad'];
            }

            // 2. Crear el Pedido (Esto se guarda en tabla 'pedidos')
            $pedido = Pedido::create([
                'cliente_id' => $request->cliente_id, 
                'nombre_cliente' => $request->nombre_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'fecha_entrega' => $request->fecha_entrega,
                'total' => $totalCalculado,
                'anticipo' => $request->anticipo, // Solo guarda la CANTIDAD como dato
                'saldo_pendiente' => $totalCalculado - $request->anticipo, 
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

            // ... dentro de public function store ...

    // 4. LOGICA DE COBRO
    if ($request->anticipo > 0 && $cajaAbierta) {
            
        // AJUSTE DE SEGURIDAD:
        // Forzamos a ver qué está llegando. Si llega vacío, ponemos 'Efectivo'.
        $metodoPago = $request->input('metodo_pago', 'Efectivo');
        $referencia = $request->input('referencia_pago', null);

        // A. Registrar el ANTICIPO
        Anticipo::create([
            'pedido_id' => $pedido->id,
            'caja_id' => $cajaAbierta->id,
            'monto' => $request->anticipo,
            'metodo_pago' => $metodoPago, 
            'referencia_pago' => $referencia,
            'user_id' => Auth::id(),
        ]);

        // B. Registrar en MOVIMIENTOS DE CAJA
        MovimientoCaja::create([
            'caja_id' => $cajaAbierta->id, 
            'user_id' => Auth::id(),
            'tipo' => 'ingreso',
            'monto' => $request->anticipo,
            'descripcion' => "Anticipo Pedido #{$pedido->id} ({$request->nombre_cliente}) - Ref: {$referencia}", // Agregamos la ref a la descripción también
            'metodo_pago' => $metodoPago, 
            'created_at' => now(),
        ]);
    }

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido registrado correctamente. Folio: ' . $pedido->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ... (Métodos edit, update y cancelar se quedan igual) ...
    public function edit($id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);
        if ($pedido->estatus == 'entregado' || $pedido->estatus == 'cancelado') {
            return back()->with('error', 'No puedes editar un pedido que ya fue entregado o cancelado.');
        }
        $clientes = Cliente::all();
        $productos = Producto::all();
        return view('pedidos.edit', compact('pedido', 'clientes', 'productos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_cliente' => 'required|string',
            'fecha_entrega' => 'required|date',
            'productos' => 'required|array|min:1',
        ]);
        $pedido = Pedido::findOrFail($id);
        DB::beginTransaction();
        try {
            $nuevoTotal = 0;
            $productosData = $request->productos;
            foreach ($productosData as $prod) {
                $nuevoTotal += $prod['precio'] * $prod['cantidad'];
            }
            $pedido->update([
                'cliente_id' => $request->cliente_id,
                'nombre_cliente' => $request->nombre_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'fecha_entrega' => $request->fecha_entrega,
                'notas_especiales' => $request->notas_especiales,
                'total' => $nuevoTotal,
                'saldo_pendiente' => $nuevoTotal - $pedido->anticipo, 
            ]);
            DetallePedido::where('pedido_id', $pedido->id)->delete();
            foreach ($productosData as $prod) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $prod['id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'especificaciones' => $prod['notas'] ?? null,
                ]);
            }
            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function cancelar($id)
    {
        $pedido = Pedido::findOrFail($id);
        if ($pedido->estatus == 'entregado') {
            return back()->with('error', 'No puedes cancelar un pedido ya entregado.');
        }
        $pedido->update([
            'estatus' => 'cancelado',
            'saldo_pendiente' => 0
        ]);
        return redirect()->route('pedidos.index')->with('success', 'Pedido #' . $id . ' cancelado exitosamente.');
    }

   /**
     * Procesa la entrega del pedido y el cobro del saldo pendiente.
     */
    public function entregar(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            // VALIDACIÓN CORREGIDA: Solo Efectivo y Tarjeta
            'metodo_pago' => 'required|in:efectivo,tarjeta', 
            'referencia_pago' => 'nullable|string|max:100',
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);
        $saldo = $pedido->saldo_pendiente; 

        DB::beginTransaction();
        try {
            if ($saldo > 0) {
                $cajaAbierta = Caja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->first();

                if (!$cajaAbierta) {
                    throw new \Exception('¡No tienes una caja abierta para recibir el pago! Por favor abre turno.');
                }

                // 1. Guardar en ANTICIPOS (Aquí se guarda el registro contable del pedido)
                Anticipo::create([
                    'pedido_id' => $pedido->id,
                    'caja_id' => $cajaAbierta->id,
                    'monto' => $saldo,
                    'metodo_pago' => ucfirst($request->metodo_pago),
                    'referencia_pago' => $request->referencia_pago, // Se guarda el folio correctamente
                    'user_id' => Auth::id(),
                ]);

                // 2. Guardar en MOVIMIENTOS DE CAJA (Para el reporte del turno)
                // Construimos una descripción detallada
                $descripcionMovimiento = "Liquidación Pedido #{$pedido->id} ({$pedido->nombre_cliente})";
                
                // Si es tarjeta y hay referencia, la agregamos al texto para que se vea en el corte
                if ($request->metodo_pago == 'tarjeta' && $request->referencia_pago) {
                    $descripcionMovimiento .= " - Ref: " . $request->referencia_pago;
                }

                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id, 
                    'user_id' => Auth::id(),
                    'tipo' => 'ingreso',
                    'monto' => $saldo,
                    'descripcion' => $descripcionMovimiento, 
                    'metodo_pago' => $request->metodo_pago,
                    'created_at' => now(),
                ]);
            }

            // Actualizar el estatus del pedido
            $pedido->update([
                'estatus' => 'entregado',
                'saldo_pendiente' => 0, 
                'anticipo' => $pedido->total // El anticipo ahora es el total porque ya se pagó todo
            ]);
            
            DB::commit();

            if ($request->has('imprimir_ticket')) {
                return redirect()->route('pedidos.index')
                    ->with('success', 'Pedido entregado y cobrado correctamente.')
                    ->with('print_ticket', $pedido->id);
            }

            return redirect()->route('pedidos.index')->with('success', 'Pedido entregado y saldo cobrado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function ticket($id)
    {
        $pedido = Pedido::with(['detalles.producto', 'cliente'])->findOrFail($id);
        return view('pedidos.ticket', compact('pedido'));
    }
}