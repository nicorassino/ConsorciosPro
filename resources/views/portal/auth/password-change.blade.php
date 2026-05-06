<x-portal-layout title="Cambio de contraseña">
    <div class="flex flex-col h-full">
        <header class="bg-white shadow-sm z-10 flex-shrink-0 border-b border-gray-100">
            <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                <div class="min-w-0">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Cambio obligatorio de contraseña</h2>
                    <p class="text-sm text-gray-500 truncate">{{ $user->nombre }}</p>
                </div>
                <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-full bg-accent-100 flex items-center justify-center text-accent-600 font-bold border border-accent-200 text-sm shrink-0">
                    {{ mb_strtoupper(mb_substr($user->nombre, 0, 2)) }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-50 min-h-0">
            <div class="max-w-lg mx-auto">
                <div class="glass-card rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                    <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                        Antes de continuar debés definir una clave personal segura.
                    </p>

                    <form method="POST" action="{{ route('portal.password.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña actual</label>
                            <input
                                id="current_password"
                                type="password"
                                name="current_password"
                                required
                                autocomplete="current-password"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                            >
                            @error('current_password')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                            >
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nueva contraseña</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                            >
                        </div>
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-lock text-sm opacity-90"></i>
                            Guardar contraseña
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</x-portal-layout>
