<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. ACLs (Deben ir primero)
            CargoSeeder::class, // SEGURO
            ModuloSeeder::class, // SEGURO
            PermisoSeeder::class, // Ejecuta después de Cargo y Módulo

            // 2. Usuario Inicial (Depende de Cargos)
            UserSeeder::class, 

            // NOTA: El permiso de CAJAS (CajaPermisoSeeder) no se ejecuta aquí.
            // NOTA: Los seeders de Catálogo/Clientes/Proveedores deben crearse
            // y llamarse aquí si se necesitan datos de prueba.
        ]);
        
    }
}

class CajaPermisoSeeder extends Seeder
{
    public function run()
    {
        $cargo_id = 1; // Super Admin
        $modulo_cajas = DB::table('modulos')->where('nombre', 'cajas')->first();

        if ($modulo_cajas) {
            DB::table('permisos')->updateOrInsert(
                ['cargo_id' => $cargo_id, 'modulo_id' => $modulo_cajas->id],
                ['mostrar' => 1, 'detalle' => 1, 'alta' => 1, 'editar' => 1, 'eliminar' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}