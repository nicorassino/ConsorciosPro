<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->string('descripcion', 500)->change();
            $table->string('factura_nombre_sistema', 500)->nullable()->after('factura_archivo');
            $table->boolean('archivo_disponible_online')->default(true)->after('factura_nombre_sistema');
            $table->date('fecha_archivado_local')->nullable()->after('archivo_disponible_online');
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->string('descripcion')->change();
            $table->dropColumn([
                'factura_nombre_sistema',
                'archivo_disponible_online',
                'fecha_archivado_local',
            ]);
        });
    }
};
