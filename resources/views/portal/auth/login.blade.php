<x-portal-guest-layout title="Ingresar">
    <div class="glass-card rounded-2xl border border-white/40 shadow-xl p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Ingresar al portal</h1>
            <p class="mt-2 text-sm text-gray-600 leading-relaxed">Usá tu email y tu número de cuenta de rentas como clave inicial.</p>
        </div>

        <form method="POST" action="{{ route('portal.login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="portal-email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    id="portal-email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                >
                @error('email')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="portal-password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input
                    id="portal-password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500"
                >
            </div>
            <button
                type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2"
            >
                <i class="fas fa-sign-in-alt text-sm opacity-90"></i>
                Ingresar
            </button>
        </form>
    </div>
</x-portal-guest-layout>
