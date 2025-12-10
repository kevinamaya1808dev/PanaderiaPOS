<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Cliente; 
use PDF;

class VentaController extends Controller
{
    // ... (El método tpv se queda igual) ...
    public function tpv()
    {
        $cajaAbierta = Caja::where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            return redirect()->route('cajas.index')->with('error', 'Debes abrir una caja antes de iniciar una venta.');
        }

        $categorias = Categoria::orderBy('nombre')->get();
        $productos = Producto::with('inventario', 'categoria')
            ->whereHas('inventario', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->orderBy('nombre')
            ->get();

        $clientes = Cliente::select('idCli', 'Nombre')->orderBy('Nombre')->get();
        
        return view('ventas.tpv', compact('cajaAbierta', 'categorias', 'productos','clientes'));
    }

    public function store(Request $request)
    {
        // 1. Validar datos básicos de la venta
        $request->validate([
            'cliente_id' => 'nullable|exists:clientes,idCli',
            'metodo_pago' => 'required|string|in:efectivo,tarjeta,pendiente',
            // CAMBIO AQUÍ: Validamos que referencia_pago sea string y opcional (o requerida si es tarjeta)
            'referencia_pago' => 'nullable|string|max:100', 
            'total' => 'required|numeric|min:0.01',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
            'detalles.*.importe' => 'required|numeric|min:0',
            'status' => 'required|string|in:Pagada,Pendiente',
        ]);

        // 2. Verificar si la caja está abierta
        $cajaAbierta = Caja::where('user_id', Auth::id())->where('estado', 'abierta')->first();
        if (!$cajaAbierta) {
            return response()->json(['message' => 'Error: La caja está cerrada.'], 400); 
        }

        DB::beginTransaction();

        try {
            // 3. Crear el registro de la Venta
            $venta = Venta::create([
                'cliente_id' => $request->cliente_id,
                'user_id' => Auth::id(),
                'fecha_hora' => now(),
                'metodo_pago' => $request->metodo_pago,
                // CAMBIO AQUÍ: Guardamos la referencia. Si no viene, se guarda como null.
                'referencia_pago' => $request->referencia_pago, 
                'total' => $request->total,
                'monto_recibido' => $request->monto_recibido ?? $request->total,
                'monto_entregado' => $request->monto_entregado ?? 0,
                'status' => $request->status,
            ]);

            // 4. Crear los Detalles de la Venta y Actualizar Stock
            foreach ($request->detalles as $detalle) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'costo_unitario' => $detalle['costo_unitario'],
                    'importe' => $detalle['importe'],
                ]);

                $inventario = Inventario::where('producto_id', $detalle['producto_id'])->first();
                if ($inventario) {
                    if ($inventario->stock < $detalle['cantidad']) {
                        throw ValidationException::withMessages([
                            'stock' => 'Stock insuficiente para el producto ID ' . $detalle['producto_id']
                        ]);
                    }
                    $inventario->decrement('stock', $detalle['cantidad']);
                } else {
                      throw ValidationException::withMessages([
                          'inventario' => 'Error: No se encontró registro de inventario para el producto ID ' . $detalle['producto_id']
                      ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Venta registrada exitosamente.', 'venta_id' => $venta->id], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error de validación.', 'errors' => $e->errors()], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error interno al procesar la venta. Verifique los logs.'], 500);
        }
    }

    // ... (El resto de métodos pdf, ticket, etc. se quedan igual) ...
    public function generarTicketPDF(Venta $venta)
    {
        $venta->load('user', 'cliente', 'detalles.producto');
        $pdf = PDF::loadView('ventas.ticket_pdf', compact('venta'));
        $pdf->setPaper([0, 0, 226.77, 800]); 
        return $pdf->stream('ticket_venta_' . $venta->id . '.pdf');
    }

    public function imprimirTicket(Venta $venta)
    {
        $urlPdf = route('ventas.ticket', $venta);
        return view('ventas.imprimir_pdf', compact('urlPdf'));
    }
    
    public function generarTicketHtml(Venta $venta)
    {
        $venta->load('user', 'cliente', 'detalles.producto');
        return view('ventas.ticket_pdf', compact('venta'));
    }
}