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
        $table->string('concepto')->nullable()->after('proveedor_id');
    });
}

public function down()
{
    Schema::table('compras', function (Blueprint $table) {
        $table->dropColumn('concepto');
    });
}
};
