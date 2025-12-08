<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // VERIFICACIÓN DE SEGURIDAD:
        // Solo agrega la columna si NO existe en la tabla 'compras'
        if (!Schema::hasColumn('compras', 'user_id')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                // Opcional: Clave foránea si la necesitas
                 $table->foreign('user_id')->references('id')->on('users');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('compras', 'user_id')) {
            Schema::table('compras', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
