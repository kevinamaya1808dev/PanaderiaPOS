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
        Schema::table('productos', function (Blueprint $table) {
            // Aquí eliminamos la columna 'costo'
            $table->dropColumn('costo');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Si hacemos rollback, volvemos a crear la columna
            // (Ajusta el tipo decimal si usabas otro)
            $table->decimal('costo', 10, 2)->default(0);
        });
    }
};