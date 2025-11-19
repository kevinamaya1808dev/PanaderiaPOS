<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // --- MÉTRICAS DE TARJETAS (Semanal y Mensual) ---
        // (Este código se mantiene igual que antes para las tarjetas de arriba)
        $metrics = [
            'weekly' => ['ingresos' => 0, 'costos' => 0, 'utilidad' => 0],
            'monthly' => ['ingresos' => 0, 'costos' => 0, 'utilidad' => 0],
        ];

        if (Auth::user()->hasPermissionTo('cargos', 'mostrar')) {
            
            // SEMANA
            $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
            $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);
            $weeklyData = DetalleVenta::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->select(DB::raw('SUM(importe) as ingresos'), DB::raw('SUM(cantidad * costo_unitario) as costos'))
                ->first();
            $metrics['weekly']['ingresos'] = $weeklyData->ingresos ?? 0;
            $metrics['weekly']['costos'] = $weeklyData->costos ?? 0;
            $metrics['weekly']['utilidad'] = $metrics['weekly']['ingresos'] - $metrics['weekly']['costos'];

            // MES
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $monthlyData = DetalleVenta::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->select(DB::raw('SUM(importe) as ingresos'), DB::raw('SUM(cantidad * costo_unitario) as costos'))
                ->first();
            $metrics['monthly']['ingresos'] = $monthlyData->ingresos ?? 0;
            $metrics['monthly']['costos'] = $monthlyData->costos ?? 0;
            $metrics['monthly']['utilidad'] = $metrics['monthly']['ingresos'] - $metrics['monthly']['costos'];
        }

        return view('dashboard', compact('metrics'));
    }

    /**
     * API para las Gráficas
     */
    public function getDashboardData(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('cargos', 'mostrar')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }      
        
        $mesSeleccionado = $request->input('month');

        // --- 1. GRÁFICA COMPARATIVA (Ventas vs Utilidad) ---
        // Consultamos DetalleVenta para tener acceso al costo_unitario
        $datosPorMes = DetalleVenta::select(
            DB::raw('MONTH(created_at) as mes'),
            DB::raw('SUM(importe) as total_ventas'),
            // Utilidad = Importe - (Cantidad * Costo)
            DB::raw('SUM(importe - (cantidad * costo_unitario)) as total_utilidad')
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        // --- 2. Gráfica de Top Productos ---
        $topProductos = DetalleVenta::join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_cantidad')
            )
            ->whereYear('detalle_ventas.created_at', now()->year)
            ->when($mesSeleccionado, function ($query) use ($mesSeleccionado) {
                return $query->whereMonth('detalle_ventas.created_at', $mesSeleccionado);
            })
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_cantidad', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'datos_por_mes' => $datosPorMes, // Enviamos ventas y utilidad juntos
            'top_productos' => $topProductos,
        ]);
    }
}