@props([
    'title' => 'Portal de Autogestión',
])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name', 'ConsorciosPro') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased font-sans text-gray-800 bg-[#F3F4F6]">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <div class="relative hidden lg:flex lg:w-[42%] xl:w-2/5 flex-col justify-between bg-gradient-to-br from-primary-900 via-primary-900 to-accent-600 p-10 text-white shadow-xl">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <div class="absolute top-10 right-10 h-40 w-40 rounded-full bg-white blur-3xl"></div>
                <div class="absolute bottom-20 left-10 h-32 w-32 rounded-full bg-emerald-400/40 blur-2xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('img/logo_cliente.png') }}"
                        alt="Cliente"
                        class="h-12 w-auto rounded-xl bg-white p-1.5 shadow-lg"
                    >
                    <div>
                        <p class="text-lg font-bold tracking-wide">Consorcios<span class="text-emerald-300">Pro</span></p>
                        <p class="text-xs text-primary-100/80 uppercase tracking-wider">Portal de autogestión</p>
                    </div>
                </div>
            </div>
            <div class="relative z-10 mt-12 space-y-4">
                <h2 class="text-2xl font-semibold leading-tight">Pagos, cupones e información de tu unidad</h2>
                <p class="text-primary-100/90 text-sm max-w-md leading-relaxed">
                    Accedé con tu email y contraseña para consultar cupones SIRO, historial de pagos y datos del consorcio.
                </p>
            </div>
            <div class="relative z-10 flex justify-center pt-8">
                <img
                    src="{{ asset('img/logo_cliente.png') }}"
                    alt="Cliente"
                    class="h-16 w-auto max-w-[85%] object-contain bg-white/95 rounded-xl px-4 py-3 shadow-md"
                >
            </div>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center p-6 sm:p-10">
            <div class="lg:hidden w-full max-w-md mb-5 text-center">
                <div class="inline-flex items-center gap-2 rounded-xl bg-primary-900 px-4 py-2.5 text-white shadow-md">
                    <img
                        src="{{ asset('img/logo_cliente.png') }}"
                        alt="Cliente"
                        class="h-8 w-auto rounded-md bg-white p-1"
                    >
                    <span class="text-sm font-bold tracking-wide">Consorcios<span class="text-emerald-300">Pro</span></span>
                </div>
            </div>
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
