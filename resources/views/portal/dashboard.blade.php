<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 p-4 md:p-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="rounded-xl bg-white p-5 shadow">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold">Portal de {{ $consorcio->nombre }}</h1>
                    <p class="text-sm text-slate-600">Unidad {{ $unidad->numero }} - {{ $portalUser->nombre }}</p>
                </div>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="rounded border border-slate-300 px-3 py-2 text-sm">Salir</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <section class="rounded-xl bg-white p-5 shadow">
                <h2 class="font-semibold">Cupones pendientes (SIRO)</h2>
                <p class="mt-1 text-xs text-slate-500">Un cupón por cada mes adeudado. Sin desglose de conceptos.</p>
                <div class="mt-3 space-y-3">
                    @forelse ($coupons as $coupon)
                        <div class="flex flex-col gap-3 rounded border p-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium">Período {{ $coupon['periodo'] }}</div>
                                <div>1er vto: ${{ number_format($coupon['monto_primer_vto'], 2, ',', '.') }}</div>
                                <div>2do vto: ${{ number_format($coupon['monto_segundo_vto'], 2, ',', '.') }}</div>
                                <div class="text-xs text-slate-600">Código pago: {{ $coupon['codigo_pago_electronico'] }} | SIRO: {{ $coupon['nro_cupon_siro'] }}</div>
                            </div>
                            <div class="shrink-0">
                                @if (! empty($coupon['pago_url']))
                                    <a
                                        href="{{ $coupon['pago_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:opacity-90 sm:w-auto"
                                        style="background-color:#1E3A5F;"
                                    >
                                        Pagar con SIRO
                                    </a>
                                @else
                                    <p class="text-xs text-slate-500">
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
                        <p class="text-sm text-slate-600">No hay deuda pendiente para emitir cupones.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-xl bg-white p-5 shadow">
                <h2 class="font-semibold">Historial de pagos acreditados</h2>
                <div class="mt-3 space-y-2 text-sm">
                    @forelse ($payments as $payment)
                        <div class="flex items-center justify-between rounded border p-2">
                            <span>{{ $payment->fecha_pago->format('d/m/Y') }} - {{ strtoupper($payment->medio_pago) }}</span>
                            <span class="font-medium">${{ number_format($payment->total_pagado, 2, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-slate-600">Todavía no hay pagos acreditados.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <section class="rounded-xl bg-white p-5 shadow">
                <h2 class="font-semibold">Reglamento y notas</h2>
                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $consorcio->nota ?: 'Sin notas cargadas.' }}</p>
            </section>
            <section class="rounded-xl bg-white p-5 shadow">
                <h2 class="font-semibold">Contacto de emergencia</h2>
                <p class="mt-2 text-sm text-slate-700">
                    {{ $consorcio->encargado_nombre }} {{ $consorcio->encargado_apellido }}<br>
                    Tel: {{ $consorcio->encargado_telefono ?: 'No informado' }}<br>
                    Horarios: {{ $consorcio->encargado_horarios ?: 'No informado' }}
                </p>
            </section>
        </div>
    </div>
</body>
</html>
