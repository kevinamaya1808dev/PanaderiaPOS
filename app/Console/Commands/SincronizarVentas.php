<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;

class SincronizarVentas extends Command
{
    protected $signature = 'sincronizar:ventas';
    protected $description = 'Envía las ventas pendientes a la nube';

    public function handle()
    {
        $this->info('Buscando ventas pendientes...');

        // 1. Buscamos ventas que tengan sincronizado = 0
        // Usamos 'with' para traer también los productos vendidos (relación hasMany)
        $ventas = Venta::where('sincronizado', 0)
                       ->with('detalles') // Asegúrate que tu modelo Venta tenga la relación 'detalles'
                       ->limit(10) // Enviamos de 10 en 10 para no saturar
                       ->get();

        if ($ventas->isEmpty()) {
            $this->info('Todo al día.');
            return;
        }

        // 2. Enviamos a la Nube
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => 'MI_CLAVE_SECRETA_PANADERIA_2025' // La misma clave que pusiste en el controlador
            ])->post('https://erp.ollintem.com.mx/api/sincronizar-ventas', [
                'ventas' => $ventas->toArray()
            ]);

            if ($response->successful()) {
                // 3. Si tuvo éxito, marcamos como sincronizadas en LOCAL
                $ids = $ventas->pluck('id');
                Venta::whereIn('id', $ids)->update(['sincronizado' => 1]);

                $this->info('Se sincronizaron ' . count($ventas) . ' ventas.');
            } else {
                $this->error('Error del servidor: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('No hay internet o servidor caído: ' . $e->getMessage());
        }
    }
}