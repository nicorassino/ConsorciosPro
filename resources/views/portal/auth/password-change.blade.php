<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio de clave</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow">
        <h1 class="text-xl font-semibold">Cambio obligatorio de contraseña</h1>
        <p class="mt-1 text-sm text-slate-600">Hola {{ $user->nombre }}, antes de continuar debés definir una clave personal.</p>

        <form method="POST" action="{{ route('portal.password.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700">Contraseña actual</label>
                <input type="password" name="current_password" required class="mt-1 w-full rounded border-slate-300">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Nueva contraseña</label>
                <input type="password" name="password" required class="mt-1 w-full rounded border-slate-300">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation" required class="mt-1 w-full rounded border-slate-300">
            </div>
            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 text-white">Guardar contraseña</button>
        </form>
    </div>
</body>
</html>
