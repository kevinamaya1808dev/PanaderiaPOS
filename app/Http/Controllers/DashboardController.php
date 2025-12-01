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
        // Limpiamos el array: Solo nos importan los ingresos
        $metrics = [
            'weekly' => ['ingresos' => 0],
            'monthly' => ['ingresos' => 0],
        ];

        if (Auth::user()->hasPermissionTo('cargos', 'mostrar')) {
            
            // SEMANA
            $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
            $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);
            
            // Consulta simplificada: SOLO suma 'importe' (Ventas)
            $weeklyData = DetalleVenta::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->select(DB::raw('SUM(importe) as ingresos'))
                ->first();
                
            $metrics['weekly']['ingresos'] = $weeklyData->ingresos ?? 0;

            // MES
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            // Consulta simplificada: SOLO suma 'importe' (Ventas)
            $monthlyData = DetalleVenta::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->select(DB::raw('SUM(importe) as ingresos'))
                ->first();
                
            $metrics['monthly']['ingresos'] = $monthlyData->ingresos ?? 0;
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

        // --- 1. GRÁFICA DE VENTAS (SOLO VENTAS) ---
        // Eliminamos el cálculo de utilidad que usaba costos
        $datosPorMes = DetalleVenta::select(
            DB::raw('MONTH(created_at) as mes'),
            DB::raw('SUM(importe) as total_ventas')
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        // --- 2. Gráfica de Top Productos ---
        // AQUI ESTÁ EL CAMBIO: Filtramos los eliminados
        $topProductos = DetalleVenta::join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_cantidad')
            )
            // --- NUEVA LÍNEA: Excluir productos con fecha de eliminación ---
            ->whereNull('productos.deleted_at') 
            // -------------------------------------------------------------
            ->whereYear('detalle_ventas.created_at', now()->year)
            ->when($mesSeleccionado, function ($query) use ($mesSeleccionado) {
                return $query->whereMonth('detalle_ventas.created_at', $mesSeleccionado);
            })
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_cantidad', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'datos_por_mes' => $datosPorMes,
            'top_productos' => $topProductos,
        ]);
    }
}