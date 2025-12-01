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
    Schema::create('anticipos', function (Blueprint $table) {
        $table->id();
        
        // Relación con el Pedido
        $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
        
        // Relación con la Caja (Para saber en qué corte debe salir)
        $table->unsignedBigInteger('caja_id'); 
        // Nota: Si usas una tabla 'cajas', descomenta la siguiente línea:
        // $table->foreign('caja_id')->references('id')->on('cajas');

        // Detalles del dinero
        $table->decimal('monto', 10, 2);
        $table->string('metodo_pago')->default('Efectivo'); // Efectivo, Tarjeta, etc.
        
        // Quién recibió el dinero
        $table->foreignId('user_id')->constrained('users');
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anticipos');
    }
};
