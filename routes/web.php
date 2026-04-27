<?php

use App\Livewire\Consorcios\ConsorcioIndex;
use App\Livewire\Liquidaciones\LiquidacionIndex;
use App\Livewire\Presupuestos\PresupuestoEditor;
use App\Livewire\Presupuestos\PresupuestoIndex;
use App\Livewire\Unidades\UnidadIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');

    Route::get('consorcios', ConsorcioIndex::class)
        ->name('consorcios.index');

    Route::get('unidades', UnidadIndex::class)
        ->name('unidades.index');

    Route::get('presupuestos', PresupuestoIndex::class)
        ->name('presupuestos.index');

    Route::get('presupuestos/nuevo', PresupuestoEditor::class)
        ->name('presupuestos.create');

    Route::get('presupuestos/{presupuesto}', PresupuestoEditor::class)
        ->name('presupuestos.show');

    Route::get('liquidaciones', LiquidacionIndex::class)
        ->name('liquidaciones.index');

    Route::view('profile', 'profile')
        ->name('profile');
});

Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware(['auth'])->name('logout');

require __DIR__.'/auth.php';
