<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <!-- Icono de advertencia -->
                <div class="text-yellow-500">
                    <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <!-- Mensaje -->
                <div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                        ¡Registra tus ingresos!
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Para empezar a controlar tu presupuesto, primero necesitas añadir tus ingresos de este mes.
                    </p>
                </div>
            </div>
            
            <!-- Botón de Acción -->
            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Resources\TransactionResource::getUrl('create', ['type' => 'ingreso']) }}"
                icon="heroicon-m-plus-circle"
                class="w-full md:w-auto"
            >
                Añadir Ingreso
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>