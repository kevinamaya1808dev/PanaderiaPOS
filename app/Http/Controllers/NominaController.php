<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\PagoNomina;
use App\Models\User;
use App\Models\Caja;           // <--- Nuevo
use App\Models\MovimientoCaja; // <--- Nuevo
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;   // <--- Nuevo
use Illuminate\Support\Facades\Auth; // <--- Nuevo

class NominaController extends Controller
{
    public function index($id, Request $request)
    {
        // Buscamos al empleado
        $empleado = Empleado::findOrFail($id);

        // Fechas
        $inicio = $request->fecha ? Carbon::parse($request->fecha) : now();
        $inicioSemana = $inicio->copy()->startOfWeek(Carbon::MONDAY);
        $finSemana = $inicio->copy()->endOfWeek(Carbon::SUNDAY);

        // Traer pagos de esa semana
        $pagos = PagoNomina::where('empleado_id', $empleado->idEmp) 
            ->whereBetween('fecha', [$inicioSemana, $finSemana])
            ->orderBy('fecha')
            ->get();

        // Total semanal
        $total = $pagos->sum(function ($pago) {
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
            'monto' => 'required|numeric|min:0.01',
            'concepto' => 'nullable|string',
            'descuento' => 'nullable|boolean',
            'pagar_de_caja' => 'nullable|boolean', // <--- Checkbox nuevo
        ]);

        $empleado = Empleado::findOrFail($id);

        DB::beginTransaction();

        try {
            // 1. Crear el registro de nómina
            PagoNomina::create([
                'empleado_id' => $empleado->idEmp, // Usamos idEmp por seguridad
                'fecha' => $request->fecha,
                'monto' => $request->monto,
                'concepto' => $request->concepto,
                'descuento' => $request->descuento ? 1 : 0,
                // Si marcamos "pagar de caja", nace ya liquidado
                'liquidado' => $request->has('pagar_de_caja') ? 1 : 0,
            ]);

            // 2. Lógica de Caja (Solo si se pidió pagar y NO es un descuento/multa)
            if ($request->has('pagar_de_caja') && !$request->has('descuento')) {
                
                // Buscar caja abierta
                $cajaAbierta = Caja::where('estado', 'abierta')->latest()->first();

                if (!$cajaAbierta) {
                    throw new \Exception('No hay ninguna caja abierta para tomar el dinero.');
                }

                // Registrar el Egreso
                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'egreso',
                    'monto' => $request->monto,
                    'descripcion' => 'Pago Nómina: ' . ($empleado->user->name ?? 'Empleado') . ' - ' . $request->concepto,
                    'metodo_pago' => 'Efectivo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return back()->with('success', 'Pago registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function liquidar($id, Request $request)
    {
        $empleado = Empleado::findOrFail($id);
        
        $inicio = Carbon::parse($request->fecha_inicio);
        $inicioSemana = $inicio->copy()->startOfWeek(Carbon::MONDAY);
        $finSemana = $inicio->copy()->endOfWeek(Carbon::SUNDAY);

        // Buscar pagos pendientes de liquidar
        $pagosPendientes = PagoNomina::where('empleado_id', $empleado->idEmp)
            ->whereBetween('fecha', [$inicioSemana, $finSemana])
            ->where('liquidado', false)
            ->get();

        if ($pagosPendientes->isEmpty()) {
            return back()->with('error', 'No había pagos pendientes para marcar.');
        }

        // Calcular cuánto dinero realmente hay que sacar de la caja
        $totalPagar = $pagosPendientes->sum(function($pago) {
            return $pago->descuento ? -$pago->monto : $pago->monto;
        });

        DB::beginTransaction();

        try {
            // Si el total es mayor a 0, intentamos descontar de caja
            if ($totalPagar > 0) {
                $cajaAbierta = Caja::where('estado', 'abierta')->latest()->first();

                if (!$cajaAbierta) {
                    throw new \Exception('No hay caja abierta para realizar la liquidación de $' . number_format($totalPagar, 2));
                }

                MovimientoCaja::create([
                    'caja_id' => $cajaAbierta->id,
                    'user_id' => Auth::id(),
                    'tipo' => 'egreso',
                    'monto' => $totalPagar,
                    'descripcion' => 'Liquidación Semanal Nómina: ' . ($empleado->user->name ?? 'Empleado'),
                    'metodo_pago' => 'Efectivo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Marcar todos los registros como pagados
            PagoNomina::where('empleado_id', $empleado->idEmp)
                ->whereBetween('fecha', [$inicioSemana, $finSemana])
                ->where('liquidado', false)
                ->update(['liquidado' => true]);

            DB::commit();
            return back()->with('success', 'Semana liquidada y dinero descontado de caja correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al liquidar: ' . $e->getMessage());
        }
    }
}