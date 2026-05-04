<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_fondos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas_bancarias')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('tipo', 20);
            $table->decimal('monto', 14, 2);
            $table->string('descripcion', 255)->nullable();
            $table->nullableMorphs('referencia');
            $table->decimal('saldo_resultante', 14, 2);
            $table->timestamps();

            $table->index(['cuenta_id', 'fecha']);
            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_fondos');
    }
};
