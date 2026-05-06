<x-portal-layout active="dashboard" title="Inicio">
    <main class="p-4 sm:p-6">
        <div class="max-w-7xl mx-auto space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <i class="fas fa-check-circle mr-2 text-emerald-600"></i>{{ session('status') }}
                </div>
            @endif

            <div class="bg-gradient-to-r from-primary-900 to-accent-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">{{ $consorcio->nombre }}</h1>
                    <p class="text-primary-100 text-sm sm:text-base max-w-2xl">
                        Unidad {{ $unidad->numero }} · {{ $portalUser->nombre }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <section class="glass-card rounded-xl p-5 sm:p-6 shadow-sm border-l-4 border-amber-400">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Cupones pendientes (SIRO)</h2>
                            <p class="mt-1 text-xs text-gray-500">Un cupón por cada mes adeudado. Sin desglose de conceptos.</p>
                        </div>
                        <div class="h-10 w-10 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-barcode text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($coupons as $coupon)
                            <div class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-white/80 p-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="font-semibold text-gray-800">Período {{ $coupon['periodo'] }}</div>
                                    <div class="text-gray-700">1er vto: <span class="font-medium">${{ number_format($coupon['monto_primer_vto'], 2, ',', '.') }}</span></div>
                                    <div class="text-gray-700">2do vto: <span class="font-medium">${{ number_format($coupon['monto_segundo_vto'], 2, ',', '.') }}</span></div>
                                    <div class="text-xs text-gray-500">Código pago: {{ $coupon['codigo_pago_electronico'] }} · SIRO: {{ $coupon['nro_cupon_siro'] }}</div>
                                </div>
                                <div class="shrink-0">
                                    @if (! empty($coupon['pago_url']))
                                        <a
                                            href="{{ $coupon['pago_url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex w-full items-center justify-center rounded-lg bg-primary-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 sm:w-auto"
                                        >
                                            <i class="fas fa-external-link-alt mr-2 text-xs opacity-90"></i>
                                            Pagar con SIRO
                                        </a>
                                    @else
                                        <p class="text-xs text-gray-500">
                                            @if (config('app.debug'))
                                                Definí <span class="font-mono">SIRO_PAYMENT_BASE_URL</span> o <span class="font-mono">SIRO_PAYMENT_URL_TEMPLATE</span> en <span class="font-mono">.env</span>.
                                            @else
                                                Pago online no disponible. Consultá medios de pago con la administración.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 py-2">No hay deuda pendiente para emitir cupones.</p>
                        @endforelse
                    </div>
                </section>

                <section class="glass-card rounded-xl p-5 sm:p-6 shadow-sm border-l-4 border-emerald-400">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Historial de pagos acreditados</h2>
                        <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-check-double text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 text-sm">
                        @forelse ($payments as $payment)
                            <div class="flex items-center justify-between rounded-lg border border-gray-100 bg-white/80 px-3 py-2.5">
                                <span class="text-gray-700">{{ $payment->fecha_pago->format('d/m/Y') }} · {{ strtoupper($payment->medio_pago) }}</span>
                                <span class="font-semibold text-gray-800">${{ number_format($payment->total_pagado, 2, ',', '.') }}</span>
                            </div>
                        @empty
                            <p class="text-gray-600 py-2">Todavía no hay pagos acreditados.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </main>
</x-portal-layout>
