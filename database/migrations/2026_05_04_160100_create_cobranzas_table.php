<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobranzas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->date('fecha_pago');
            $table->decimal('monto_capital', 12, 2);
            $table->decimal('monto_interes', 12, 2)->default(0);
            $table->decimal('total_pagado', 12, 2);
            $table->string('medio_pago', 40)->default('siro');
            $table->string('comprobante_path')->nullable();
            $table->foreignId('liquidacion_detalle_id')->nullable()->constrained('liquidacion_detalles')->nullOnDelete();
            $table->timestamps();

            $table->index(['consorcio_id', 'fecha_pago']);
            $table->index(['unidad_id', 'fecha_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobranzas');
    }
};
