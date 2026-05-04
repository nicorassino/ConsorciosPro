<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasto_concepto_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gasto_id')->constrained('gastos')->cascadeOnDelete();
            $table->foreignId('concepto_presupuesto_id')->constrained('concepto_presupuestos')->cascadeOnDelete();
            $table->decimal('importe_asignado', 12, 2);
            $table->timestamps();

            $table->unique(['gasto_id', 'concepto_presupuesto_id'], 'gcp_gasto_concepto_uq');
            $table->index('gasto_id', 'gcp_gasto_idx');
            $table->index('concepto_presupuesto_id', 'gcp_concepto_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_concepto_presupuesto');
    }
};
