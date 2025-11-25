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
    Schema::table('compras', function (Blueprint $table) {
        $table->string('responsable_nombre')->nullable();
    });
}

public function down()
{
    Schema::table('compras', function (Blueprint $table) {
        $table->dropColumn('responsable_nombre');
    });
}

};
