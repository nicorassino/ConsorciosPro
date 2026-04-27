<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->date('periodo');
            $table->string('estado', 32)->default('borrador');
            $table->foreignId('presupuesto_anterior_id')->nullable()->constrained('presupuestos')->nullOnDelete();
            $table->unsignedTinyInteger('dia_primer_vencimiento_real')->nullable();
            $table->unsignedTinyInteger('dia_segundo_vencimiento_real')->nullable();
            $table->decimal('recargo_segundo_vto_real', 5, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['consorcio_id', 'periodo']);
            $table->index(['consorcio_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
