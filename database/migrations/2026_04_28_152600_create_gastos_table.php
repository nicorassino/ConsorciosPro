<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consorcio_id')->constrained('consorcios')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('nro_orden');
            $table->string('descripcion');
            $table->decimal('importe', 12, 2);
            $table->date('fecha_factura');
            $table->date('periodo');
            $table->string('estado', 32)->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->string('comprobante_pago')->nullable();
            $table->string('factura_archivo')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['consorcio_id', 'estado']);
            $table->index(['consorcio_id', 'periodo']);
            $table->index(['consorcio_id', 'nro_orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
