<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ***** AÑADE ESTA LÍNEA *****
        Schema::disableForeignKeyConstraints();
        Schema::table('productos', function (Blueprint $table) {
            // 1. Borra la regla única anterior (la que te dio el error 1062)
            $table->dropUnique('productos_categoria_id_nombre_unique');
            // 2. Crea la nueva regla "inteligente" que incluye deleted_at
            // Esto permite ('CONCHA', 1, '2025-11-10') y ('CONCHA', 1, NULL)
            $table->unique(['categoria_id', 'nombre', 'deleted_at']);
        });
        // ***** Y AÑADE ESTA LÍNEA *****
        Schema::enableForeignKeyConstraints();
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ***** AÑADE ESTA LÍNEA *****
        Schema::disableForeignKeyConstraints();
        Schema::table('productos', function (Blueprint $table) {
            // Para deshacer, borramos la regla nueva
            $table->dropUnique(['categoria_id', 'nombre', 'deleted_at']);
            // Y volvemos a poner la regla antigua
            $table->unique(['categoria_id', 'nombre'], 'productos_categoria_id_nombre_unique');
        });
        // ***** Y AÑADE ESTA LÍNEA *****
        Schema::enableForeignKeyConstraints();
    }
};