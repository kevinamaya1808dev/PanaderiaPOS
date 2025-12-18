<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venta; // Asegúrate de importar tus modelos
use App\Models\DetalleVenta; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizacionController extends Controller
{
    public function recibirVentas(Request $request)
    {
        // Validamos que venga un Token de seguridad simple (puedes mejorarlo luego)
        if ($request->header('X-API-KEY') !== 'MI_CLAVE_SECRETA_PANADERIA_2025') {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $ventasLocales = $request->input('ventas');
        $guardadas = 0;

        DB::beginTransaction(); // Importante: Todo o nada
        try {
            foreach ($ventasLocales as $ventaData) {

                // Verificamos si ya existe esa venta de esa sucursal para no duplicar
                $existe = Venta::where('codigo_sucursal', 'SUCURSAL_1')
                               ->where('id_venta_local', $ventaData['id'])
                               ->exists();

                if (!$existe) {
                    // Creamos la venta en la Nube
                    // OJO: No pasamos el ID, dejamos que la Nube cree uno nuevo
                    $nuevaVenta = new Venta();
                    $nuevaVenta->fill($ventaData); // Asegúrate que los campos estén en $fillable en el Modelo
                    $nuevaVenta->id_venta_local = $ventaData['id']; // Guardamos la referencia
                    $nuevaVenta->codigo_sucursal = 'SUCURSAL_1';
                    $nuevaVenta->sincronizado = true; // En la nube ya nace sincronizada
                    $nuevaVenta->save();

                    // Guardamos los detalles (productos vendidos)
                    foreach ($ventaData['detalles'] as $detalleData) {
                        $nuevoDetalle = new DetalleVenta();
                        $nuevoDetalle->fill($detalleData);
                        $nuevoDetalle->venta_id = $nuevaVenta->id; // Usamos el NUEVO ID de la nube
                        $nuevoDetalle->save();
                    }
                    $guardadas++;
                }
            }
            DB::commit();
            return response()->json(['status' => 'ok', 'guardadas' => $guardadas]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage()); // Revisa storage/logs/laravel.log si falla
            return response()->json(['error' => 'Error al guardar', 'msg' => $e->getMessage()], 500);
        }
    }
}