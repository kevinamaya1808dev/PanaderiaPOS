<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('ventas', function (Blueprint $table) {
        // Este campo servirá en LOCAL para saber si ya se envió
        $table->boolean('sincronizado')->default(false)->after('total'); 

        // Este campo servirá en la NUBE para saber cuál era el ID original en la caja
        $table->unsignedBigInteger('id_venta_local')->nullable()->after('sincronizado');

        // Este campo servirá en la NUBE para saber de qué sucursal viene (si tienes varias)
        $table->string('codigo_sucursal')->nullable()->after('id_venta_local');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
