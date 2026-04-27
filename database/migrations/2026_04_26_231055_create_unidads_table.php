<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->string('numero');
            $table->string('nro_ph')->nullable();
            $table->decimal('coeficiente', 8, 6)->nullable();
            $table->string('nomenclatura_catastral')->nullable();
            $table->string('nro_cuenta_rentas')->nullable();
            $table->boolean('tiene_cochera')->default(false);
            $table->string('nro_cochera')->nullable();
            $table->string('estado_ocupacion', 32)->default('propietario_residente');
            $table->string('nro_cupon_siro')->nullable();
            $table->string('codigo_pago_electronico')->nullable();
            $table->string('recibos_a_nombre_de', 32)->default('propietario');
            $table->string('condicion_iva', 32)->default('consumidor_final');
            $table->string('email_expensas_ordinarias', 500)->nullable();
            $table->string('email_expensas_extraordinarias', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
