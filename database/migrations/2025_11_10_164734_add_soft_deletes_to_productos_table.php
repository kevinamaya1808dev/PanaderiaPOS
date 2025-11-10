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
        // Esto añade la columna 'deleted_at'
        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Esto permite deshacer el cambio
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};