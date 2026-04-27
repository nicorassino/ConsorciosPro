<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consorcios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('cuit')->unique();
            $table->string('banco')->nullable();
            $table->string('nro_cuenta_bancaria')->nullable();
            $table->string('convenio')->nullable();
            $table->string('sucursal')->nullable();
            $table->string('digito_verificador')->nullable();
            $table->string('cbu', 22)->nullable();
            $table->string('condicion_iva', 32)->default('no_alcanzado');
            $table->string('nro_cuenta_rentas')->nullable();
            $table->string('nomenclatura_catastral')->nullable();
            $table->string('nro_matricula')->nullable();
            $table->date('fecha_inscripcion_reglamento')->nullable();
            $table->string('unidad_facturacion_aguas')->nullable();
            $table->boolean('tiene_cocheras')->default(false);
            $table->string('encargado_nombre')->nullable();
            $table->string('encargado_apellido')->nullable();
            $table->string('encargado_telefono')->nullable();
            $table->text('encargado_horarios')->nullable();
            $table->text('encargado_dias')->nullable();
            $table->string('encargado_empresa_servicio')->nullable();
            $table->string('nombre_administracion')->nullable();
            $table->string('logo_administracion')->nullable();
            $table->text('texto_medios_pago')->nullable();
            $table->unsignedTinyInteger('dia_primer_vencimiento')->nullable();
            $table->unsignedTinyInteger('dia_segundo_vencimiento')->nullable();
            $table->decimal('recargo_segundo_vto', 5, 2)->nullable();
            $table->text('nota')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consorcios');
    }
};
