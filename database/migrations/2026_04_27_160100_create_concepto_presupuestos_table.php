<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concepto_presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('rubro', 32)->default('otros');
            $table->text('descripcion')->nullable();
            $table->decimal('monto_total', 12, 2);
            $table->unsignedInteger('cuotas_total')->default(1);
            $table->unsignedInteger('cuota_actual')->default(1);
            $table->string('tipo', 32)->default('ordinario');
            $table->boolean('aplica_cocheras')->default(false);
            $table->decimal('monto_factura_real', 12, 2)->nullable();
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();

            $table->index(['presupuesto_id', 'tipo']);
            $table->index(['presupuesto_id', 'rubro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concepto_presupuestos');
    }
};
