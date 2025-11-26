<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::table('empleados', function (Blueprint $table) {
        $table->boolean('requiere_acceso')->default(false); 
    });
}

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['requiere_acceso','user_id']);
        });
    }
};
