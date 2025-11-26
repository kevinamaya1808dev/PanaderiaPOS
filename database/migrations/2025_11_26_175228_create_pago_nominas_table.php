<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::create('pago_nominas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('empleado_id');
        $table->date('fecha');
        $table->decimal('monto', 10, 2);
        $table->string('concepto')->nullable();
        $table->boolean('descuento')->default(false);
        $table->timestamps();

        // AQUÍ ESTÁ EL CAMBIO:
        // Cambiamos references('id') por references('idEmp')
        $table->foreign('empleado_id')
              ->references('idEmp') // <--- Apuntamos al nombre real de tu columna
              ->on('empleados')
              ->cascadeOnDelete();
    });
}

    public function down()
    {
        // Asegúrate de borrar la tabla con el nombre correcto también
        Schema::dropIfExists('pago_nominas');
    }
};