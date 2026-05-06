<x-app-layout active="profile">
    <div class="flex flex-col h-full">
        <header class="bg-white shadow-sm z-10 flex-shrink-0">
            <div class="flex items-center justify-between px-6 py-4">
                <h2 class="text-2xl font-bold text-gray-800">Perfil</h2>
                <x-user-menu />
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow-sm rounded-xl border border-gray-100">
                    <div class="max-w-xl">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-sm rounded-xl border border-gray-100">
                    <div class="max-w-xl">
                        <livewire:profile.update-password-form />
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow-sm rounded-xl border border-gray-100">
                    <div class="max-w-xl">
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
