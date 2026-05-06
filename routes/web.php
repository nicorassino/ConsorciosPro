<?php

use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalPasswordController;
use App\Livewire\Finanzas\ReporteIndex;
use App\Livewire\Consorcios\ConsorcioIndex;
use App\Livewire\Gastos\GastoEditor;
use App\Livewire\Gastos\GastoIndex;
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

    Route::get('gastos', GastoIndex::class)
        ->name('gastos.index');

    Route::get('gastos/nuevo', GastoEditor::class)
        ->name('gastos.create');

    Route::get('gastos/{gasto}', GastoEditor::class)
        ->name('gastos.show');

    Route::get('reportes', ReporteIndex::class)
        ->name('reportes.index');

    Route::view('profile', 'profile')
        ->name('profile');
});

Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware(['auth'])->name('logout');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:portal')->group(function () {
        Route::get('login', [PortalAuthController::class, 'create'])->name('login');
        Route::post('login', [PortalAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:portal'])->group(function () {
        Route::get('password/change', [PortalPasswordController::class, 'edit'])->name('password.edit');
        Route::put('password/change', [PortalPasswordController::class, 'update'])->name('password.update');
        Route::post('logout', [PortalAuthController::class, 'destroy'])->name('logout');
    });

    Route::middleware(['auth:portal', 'force-password-change'])->group(function () {
        Route::get('dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('reglamento-y-notas', [PortalDashboardController::class, 'notes'])->name('notes');
        Route::get('contacto', [PortalDashboardController::class, 'contact'])->name('contact');
    });
});

require __DIR__.'/auth.php';
