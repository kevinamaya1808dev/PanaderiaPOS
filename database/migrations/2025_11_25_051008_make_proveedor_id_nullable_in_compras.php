<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']); // quitamos relación anterior
            $table->unsignedBigInteger('proveedor_id')->nullable()->change(); // ahora permite NULL
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->unsignedBigInteger('proveedor_id')->nullable(false)->change(); // volvemos a requerido
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->restrictOnDelete();
        });
    }
};