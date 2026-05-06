<x-portal-layout active="notes" title="Reglamento y notas">
    <main class="p-4 sm:p-6">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="bg-gradient-to-r from-primary-900 to-accent-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Reglamento y notas</h1>
                    <p class="text-primary-100 text-sm sm:text-base">Consorcio {{ $consorcio->nombre }} · Unidad {{ $unidad->numero }}</p>
                </div>
            </div>

            <section class="glass-card rounded-xl p-5 sm:p-6 shadow-sm border-l-4 border-blue-400">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h2 class="text-lg font-semibold text-gray-800">Información para propietarios e inquilinos</h2>
                    <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                        <i class="fas fa-file-alt text-lg"></i>
                    </div>
                </div>
                <p class="mt-3 text-sm text-gray-700 whitespace-pre-line leading-relaxed">
                    {{ $consorcio->nota ?: 'Sin notas cargadas.' }}
                </p>
            </section>
        </div>
    </main>
</x-portal-layout>
