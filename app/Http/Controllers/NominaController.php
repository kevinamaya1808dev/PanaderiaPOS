<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\PagoNomina;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

class NominaController extends Controller
{
    public function index($id, Request $request)
{
    // Buscamos al empleado
    // IMPORTANTE: Asegúrate de que tu Modelo Empleado sepa que su llave es 'idEmp'
    $empleado = Empleado::findOrFail($id);

    // Fechas (esto está bien)
    $inicio = $request->fecha ? Carbon::parse($request->fecha) : now();
    $inicioSemana = $inicio->copy()->startOfWeek(Carbon::MONDAY);
    $finSemana = $inicio->copy()->endOfWeek(Carbon::SUNDAY);

    // --- CORRECCIÓN AQUÍ ---
    // Cambiamos '$empleado->id' por '$empleado->idEmp'
    $pagos = PagoNomina::where('empleado_id', $empleado->idEmp) 
        ->whereBetween('fecha', [$inicioSemana, $finSemana])
        ->orderBy('fecha')
        ->get();

    // Total semanal
    $total = $pagos->sum(function ($pago) {
        // Lógica simple: Si es descuento resta, si no suma
        return $pago->descuento ? -$pago->monto : $pago->monto;
    });

    return view('nomina.index', compact(
        'empleado',
        'pagos',
        'inicio',
        'inicioSemana',
        'finSemana',
        'total'
    ));
}
    public function store($id, Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'monto' => 'required|numeric',
            'concepto' => 'nullable|string',
            'descuento' => 'nullable|boolean',
        ]);

        PagoNomina::create([
            'empleado_id' => $id,
            'fecha' => $request->fecha,
            'monto' => $request->monto,
            'concepto' => $request->concepto,
            'descuento' => $request->descuento ? 1 : 0,
        ]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function liquidar($id, Request $request)
{
    // Buscamos al empleado por su ID personalizado
    $empleado = Empleado::findOrFail($id);
    
    // Definir fechas
    $inicio = Carbon::parse($request->fecha_inicio);
    $inicioSemana = $inicio->copy()->startOfWeek(Carbon::MONDAY);
    $finSemana = $inicio->copy()->endOfWeek(Carbon::SUNDAY);

    // Actualizar TODOS los pagos de esa semana que no estén liquidados
    // Usamos update() directamente, es más rápido y limpio
    $afectados = PagoNomina::where('empleado_id', $empleado->idEmp)
        ->whereBetween('fecha', [$inicioSemana, $finSemana])
        ->where('liquidado', false)
        ->update(['liquidado' => true]);

    if ($afectados == 0) {
        return back()->with('error', 'No había pagos pendientes para marcar.');
    }

    return back()->with('success', 'Semana marcada como PAGADA correctamente.');
    }
}
