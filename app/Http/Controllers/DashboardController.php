<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Muestra la página principal del dashboard que contendrá las gráficas.
     */
    public function index()
    {
        // Esta función simplemente retorna la vista.
        // El JavaScript en esa vista se encargará de pedir los datos.
        return view('dashboard');
    }

    /**
     * Proporciona los datos en formato JSON para las gráficas.
     * Esta es la 'API' que llamará nuestro JavaScript.
     */
    public function getDashboardData(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('cargos', 'mostrar')) {
        // Retorna un error 403 (Prohibido)
        return response()->json(['error' => 'No autorizado'], 403);
    }      
        // Recibe un 'month' (mes) opcional desde la petición
        $mesSeleccionado = $request->input('month'); // ej. '12' para Diciembre

        // --- 1. Gráfica de Ventas por Mes (Siempre se calcula para todo el año) ---
        $ventasPorMes = Venta::select(
            DB::raw('MONTH(fecha_hora) as mes'),
            DB::raw('SUM(total) as total_ventas')
        )
        ->whereYear('fecha_hora', now()->year) // Solo de este año
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        // --- 2. Gráfica de Top 5 Productos ---
        $topProductos = DetalleVenta::join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id') // Unir con ventas para filtrar por fecha
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_cantidad')
            )
            ->whereYear('ventas.fecha_hora', now()->year) // Solo de este año
            
            // Si se seleccionó un mes, filtra por ese mes
            ->when($mesSeleccionado, function ($query) use ($mesSeleccionado) {
                return $query->whereMonth('ventas.fecha_hora', $mesSeleccionado);
            })
            
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_cantidad', 'desc')
            ->limit(5)
            ->get();

        // --- 3. Devolver los datos como JSON ---
        return response()->json([
            'ventas_por_mes' => $ventasPorMes,
            'top_productos' => $topProductos,
        ]);
    }
}