<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RespaldoVentasMail;
use Carbon\Carbon;

class ArchivarVentasMensuales extends Command
{
    protected $signature = 'ventas:archivar';
    protected $description = 'Exporta ventas, envía correo y limpia BD';

    public function handle()
    {
        // --- CAMBIO 1: FECHA DE PRODUCCIÓN ---
        // Esto selecciona todo lo anterior al inicio de este mes.
        // Ejemplo: Si hoy es 1 de Enero, selecciona todo lo anterior al 1 de Diciembre.
        $fechaCorte = Carbon::now()->subMonth()->startOfMonth(); 

        $nombreArchivo = 'respaldo_ventas_' . $fechaCorte->format('Y_m') . '.csv';
        $ruta = storage_path('app/archivos/' . $nombreArchivo);

        if (!file_exists(dirname($ruta))) {
            mkdir(dirname($ruta), 0755, true);
        }

        $this->info("Iniciando respaldo de datos anteriores a: " . $fechaCorte->toDateString());

        DB::beginTransaction();

        try {
            $handle = fopen($ruta, 'w');
            
            // BOM para acentos en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, ['ID Venta', 'Fecha', 'Cliente', 'Total', 'Producto', 'Cantidad', 'Precio Unitario']);

            Venta::with(['detalles.producto', 'cliente']) 
                ->where('created_at', '<', $fechaCorte)
                ->chunk(100, function ($ventas) use ($handle) {
                    
                    foreach ($ventas as $venta) {
                        $esPrimerProducto = true; 

                        foreach ($venta->detalles as $detalle) {
                            fputcsv($handle, [
                                $esPrimerProducto ? $venta->id : '',
                                $esPrimerProducto ? $venta->created_at->toDateString() : '',
                                $esPrimerProducto ? ($venta->cliente->nombre ?? 'Publico General') : '',
                                $esPrimerProducto ? $venta->total : '',
                                $detalle->producto->nombre ?? 'Producto Eliminado',
                                $detalle->cantidad,
                                $detalle->precio_unitario 
                            ]);
                            $esPrimerProducto = false; 
                        }
                    }
                });

            fclose($handle);

            // ENVIAR CORREO
            Mail::to('kairos@ollintem.com.mx')->send(new RespaldoVentasMail($ruta));
            $this->info("Correo enviado.");

            // LIMPIAR BD
            $borradas = Venta::where('created_at', '<', $fechaCorte)->delete();
            $this->info("Se eliminaron $borradas ventas antiguas.");
            
            DB::commit();

            // --- CAMBIO 2: LIMPIEZA DE ARCHIVO ---
            // Ya está descomentado para que no llenes tu servidor
            if (file_exists($ruta)) {
                unlink($ruta);
                $this->info("Archivo temporal eliminado del servidor.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            // Si falla, intentamos borrar el archivo corrupto/incompleto si se creó
            if (file_exists($ruta)) unlink($ruta);
            $this->error("Error: " . $e->getMessage());
        }
    }
}