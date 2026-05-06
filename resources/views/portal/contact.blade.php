<x-portal-layout active="contact" title="Contacto y encargado">
    <main class="p-4 sm:p-6">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="bg-gradient-to-r from-primary-900 to-accent-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Contacto y encargado</h1>
                    <p class="text-primary-100 text-sm sm:text-base">Consorcio {{ $consorcio->nombre }} · Unidad {{ $unidad->numero }}</p>
                </div>
            </div>

            <section class="glass-card rounded-xl p-5 sm:p-6 shadow-sm border-l-4 border-rose-400">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Datos del encargado</h2>
                    <div class="h-10 w-10 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                        <i class="fas fa-phone-alt text-lg"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-700 leading-relaxed space-y-1">
                    <p><span class="font-semibold">Nombre:</span> {{ $consorcio->encargado_nombre ?: 'No informado' }} {{ $consorcio->encargado_apellido ?: '' }}</p>
                    <p><span class="font-semibold">Teléfono:</span> {{ $consorcio->encargado_telefono ?: 'No informado' }}</p>
                    <p><span class="font-semibold">Horarios:</span> {{ $consorcio->encargado_horarios ?: 'No informado' }}</p>
                </div>
            </section>
        </div>
    </main>
</x-portal-layout>
