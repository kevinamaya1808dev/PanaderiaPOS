<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabla Principal: PEDIDOS
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            
            // Relación con Clientes (Usando idCli)
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')
                  ->references('idCli') // <--- Correcto según tu indicación
                  ->on('clientes');  

            // Datos del Cliente Casual
            $table->string('nombre_cliente'); 
            $table->string('telefono_cliente')->nullable(); 

            // Datos Financieros y Fechas
            $table->dateTime('fecha_entrega');
            $table->decimal('total', 10, 2);
            $table->decimal('anticipo', 10, 2); 
            
            $table->enum('estatus', ['pendiente', 'en_proceso', 'listo', 'entregado', 'cancelado'])->default('pendiente');
            $table->text('notas_especiales')->nullable(); 
            
            // Relación con Usuario (Cajero)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->timestamps();
        });

        // 2. Tabla Secundaria: DETALLE DE PEDIDOS
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            
            // --- OJO AQUÍ CON LOS PRODUCTOS ---
            // Si tu tabla de productos usa 'id' normal, deja esta línea:
            $table->foreignId('producto_id')->constrained('productos');

            // PERO, si usa 'idProd', BORRA la línea de arriba y USA estas dos:
            // $table->unsignedBigInteger('producto_id');
            // $table->foreign('producto_id')->references('idProd')->on('productos');
            
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->string('especificaciones')->nullable(); 
        });
    }

    public function down(): void
    {
        // El orden importa: Primero borras la hija, luego la padre
        Schema::dropIfExists('detalle_pedidos');
        Schema::dropIfExists('pedidos');
    }
};