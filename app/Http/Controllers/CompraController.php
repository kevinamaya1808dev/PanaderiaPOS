<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;          // <--- Faltaba esta


class CompraController extends Controller
{
    /**
     * Muestra listado.
     */
    public function index()
{
    // Código correcto para COMPRAS (no para Cajas)
    $compras = Compra::with(['proveedor', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

    return view('compras.index', compact('compras'));
}
    /**
     * Form para crear.
     */
    public function create()
    {
        $proveedores = Proveedor::all();
        return view('compras.create', compact('proveedores'));
    }

    /**
     * Guardar compra o gasto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_movimiento' => 'required|in:compra,gasto',
            'proveedor_id' => [
                'required_if:tipo_movimiento,compra',
                'nullable',
                'exists:proveedores,id'
            ],
            'concepto' => 'nullable|string|max:255',   // <-- AGREGADO
            'descripcion' => 'nullable|string|max:255',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,credito,transferencia',
            'total' => 'required|numeric|min:0.01',
            'responsable_nombre' => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            // Determinar descripción para el movimiento
            $descripcionFinal = $request->descripcion;

            // Crear el registro
            $compra = Compra::create([
                'user_id' => Auth::id(),
                'proveedor_id' => $request->tipo_movimiento === 'compra'
                    ? $request->proveedor_id
                    : null,
                'concepto' => $request->tipo_movimiento === 'gasto'   // <-- NUEVO
                    ? $request->concepto
                    : null,
                'metodo_pago' => $request->metodo_pago,
                'total' => $request->total,
                'descripcion' => $descripcionFinal,
            ]);

            // Descuento en caja si aplica
            if ($request->metodo_pago === 'efectivo') {

                $cajaAbierta = Caja::where('user_id', Auth::id())
                                    ->where('estado', 'abierta')
                                    ->first();

                if ($cajaAbierta) {

                    MovimientoCaja::create([
                        'caja_id' => $cajaAbierta->id,
                        'user_id' => Auth::id(),
                        'tipo' => 'egreso',
                        'monto' => $request->total,
                        'descripcion' => $request->tipo_movimiento === 'gasto'
                            ? "Gasto: " . $request->concepto
                            : "Compra Prov. #" . $compra->id . ': ' . $descripcionFinal,
                        'metodo_pago' => $request->metodo_pago,
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('compras.index')
                ->with('success', 'Registro guardado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Editar.
     */
    public function edit(Compra $compra)
    {
        $proveedores = Proveedor::all();
        return view('compras.edit', compact('compra', 'proveedores'));
    }

    /**
     * Actualizar.
     */
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'concepto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,credito,transferencia',
            'total' => 'required|numeric|min:0.01',
        ]);

        $compra->update($request->all());

        return redirect()->route('compras.index')->with('success', 'Registro actualizado correctamente.');
    }

    /**
     * Eliminar.
     */
    public function destroy(Compra $compra)
    {
        $compra->delete();
        return redirect()->route('compras.index')->with('success', 'Registro eliminado correctamente.');
    }
   public function show($id)
    {
        // 1. Buscamos la caja con su usuario asociado
        $caja = Caja::with('user')->findOrFail($id);

        // 2. Obtenemos los MOVIMIENTOS (Esto es clave para tu tabla de gastos)
        $movimientos = MovimientoCaja::where('caja_id', $caja->id)
                                    ->orderBy('created_at', 'desc')
                                    ->get();

        // 3. Obtenemos las VENTAS con sus detalles para la tabla azul
        $ventas = Venta::where('caja_id', $caja->id)
                       ->with(['detalles.producto']) // Cargamos productos para no tener errores
                       ->orderBy('fecha_hora', 'desc')
                       ->get();

        // 4. CALCULAMOS LOS TOTALES (Tu vista pide estas variables específicas)
        
        // Suma de ventas SOLO en efectivo (para el balance)
        $totalVentasEfectivo = $ventas->where('metodo_pago', 'efectivo')->sum('total');

        // Suma de egresos/gastos (para restar en el balance)
        $egresos = $movimientos->where('tipo', 'egreso')->sum('monto');

        // 5. Generar el nombre del turno (Matutino, Vespertino, etc.)
        $horaApertura = $caja->created_at->format('H');
        $nombreTurno = 'Turno General';
        if($horaApertura < 12) $nombreTurno = 'Turno Matutino';
        elseif($horaApertura < 18) $nombreTurno = 'Turno Vespertino';
        else $nombreTurno = 'Turno Nocturno';

        // 6. Retornamos la vista con TODAS las variables que usaste en el Blade
        return view('historial_cajas.show', compact(
            'caja', 
            'movimientos', 
            'ventas', 
            'totalVentasEfectivo', 
            'egresos', 
            'nombreTurno'
        ));
    }
}


