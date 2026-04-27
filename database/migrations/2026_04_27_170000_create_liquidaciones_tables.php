<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->unique()->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->date('periodo');
            $table->decimal('total_ordinario', 12, 2)->default(0);
            $table->decimal('total_extraordinario', 12, 2)->default(0);
            $table->decimal('total_general', 12, 2)->default(0);
            $table->date('fecha_primer_vto');
            $table->date('fecha_segundo_vto')->nullable();
            $table->decimal('monto_segundo_vto', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['consorcio_id', 'periodo']);
        });

        Schema::create('liquidacion_conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liquidacion_id')->constrained('liquidaciones')->cascadeOnDelete();
            $table->foreignId('concepto_presupuesto_id')->nullable()->constrained('concepto_presupuestos')->nullOnDelete();
            $table->string('nombre');
            $table->decimal('monto_total', 12, 2);
            $table->string('tipo', 32);
            $table->string('metodo_distribucion', 32);
            $table->boolean('solo_cocheras')->default(false);
            $table->timestamps();
        });

        Schema::create('liquidacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liquidacion_concepto_id')->constrained('liquidacion_conceptos')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->decimal('coeficiente_aplicado', 8, 6)->default(0);
            $table->decimal('monto_calculado', 12, 2)->default(0);
            $table->boolean('excluido')->default(false);
            $table->decimal('porcentaje_manual', 8, 6)->nullable();
            $table->timestamps();

            $table->unique(['liquidacion_concepto_id', 'unidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidacion_detalles');
        Schema::dropIfExists('liquidacion_conceptos');
        Schema::dropIfExists('liquidaciones');
    }
};
