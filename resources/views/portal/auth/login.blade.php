<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal de Autogestión</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow">
        <h1 class="text-xl font-semibold">Portal de Autogestión</h1>
        <p class="mt-1 text-sm text-slate-600">Ingresá con tu email y tu número de cuenta de rentas como clave inicial.</p>

        <form method="POST" action="{{ route('portal.login.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded border-slate-300">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Contraseña</label>
                <input type="password" name="password" required class="mt-1 w-full rounded border-slate-300">
            </div>
            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 text-white">Ingresar</button>
        </form>
    </div>
</body>
</html>
