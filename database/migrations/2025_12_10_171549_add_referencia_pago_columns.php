<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        // Agregamos la columna a la tabla VENTAS
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('referencia_pago')->nullable()->after('metodo_pago');
        });

        // Agregamos la columna a la tabla ANTICIPOS (como pediste)
        Schema::table('anticipos', function (Blueprint $table) {
            $table->string('referencia_pago')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('referencia_pago');
        });
        Schema::table('anticipos', function (Blueprint $table) {
            $table->dropColumn('referencia_pago');
        });
    }
};
