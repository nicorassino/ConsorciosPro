<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('nombre', 200);
            $table->string('dni', 20)->nullable();
            $table->string('direccion_postal', 500)->nullable();
            $table->string('email', 500)->nullable();
            $table->string('telefono', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('inquilinos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('nombre', 200)->nullable();
            $table->string('apellido', 200)->nullable();
            $table->string('telefono', 200)->nullable();
            $table->string('email', 500)->nullable();
            $table->string('direccion_postal', 500)->nullable();
            $table->date('fecha_fin_contrato')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('inmobiliarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('nombre', 200)->nullable();
            $table->string('apellido', 200)->nullable();
            $table->string('telefono', 200)->nullable();
            $table->string('email', 500)->nullable();
            $table->string('direccion', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('contactos_alternativos', function (Blueprint $table) {
            $table->id();
            $table->string('contactable_type', 32);
            $table->unsignedBigInteger('contactable_id');
            $table->string('nombre', 200);
            $table->string('telefono', 200)->nullable();
            $table->string('email', 500)->nullable();
            $table->timestamps();

            $table->index(['contactable_type', 'contactable_id'], 'contactos_alternativos_contactable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos_alternativos');
        Schema::dropIfExists('inmobiliarias');
        Schema::dropIfExists('inquilinos');
        Schema::dropIfExists('propietarios');
    }
};
