<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->string('cbu', 22);
            $table->decimal('saldo_actual', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['consorcio_id', 'nombre']);
            $table->unique(['consorcio_id', 'cbu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_bancarias');
    }
};
