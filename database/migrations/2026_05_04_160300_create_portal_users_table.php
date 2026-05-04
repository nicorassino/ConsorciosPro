<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->string('nombre', 200);
            $table->string('email', 191)->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('password_changed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['unidad_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_users');
    }
};
