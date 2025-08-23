<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Resumen Financiero del Mes ({{ now()->translatedFormat('F') }})
            </h2>

            <!-- Resumen general -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-green-100 dark:bg-green-900/50 p-6 rounded-lg shadow">
                    <div class="text-sm font-medium text-green-700 dark:text-green-400">Ingresos Totales</div>
                    <div class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-200">
                        {{ number_format($this->data['totalIncome'], 2, ',', '.') }} COP
                    </div>
                </div>
                <div class="bg-red-100 dark:bg-red-900/50 p-6 rounded-lg shadow">
                    <div class="text-sm font-medium text-red-700 dark:text-red-400">Gastos Totales</div>
                    <div class="mt-1 text-3xl font-semibold text-red-900 dark:text-red-200">
                        {{ number_format($this->data['totalExpenses'], 2, ',', '.') }} COP
                    </div>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900/50 p-6 rounded-lg shadow">
                    <div class="text-sm font-medium text-blue-700 dark:text-blue-400">Balance</div>
                    <div class="mt-1 text-3xl font-semibold text-blue-900 dark:text-blue-200">
                        {{ number_format($this->data['balance'], 2, ',', '.') }} COP
                    </div>
                </div>
            </div>

            <hr class="my-6 dark:border-gray-700">

            <!-- Control de Presupuesto 50/20/30 -->
            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-4">
                Control de Presupuesto (Regla 50/20/30)
            </h3>
            <div class="space-y-6">
                @foreach ($this->data['recommended'] as $type => $recommendedAmount)
                    @php
                        $actualAmount = $this->data['actual'][$type] ?? 0;
                        $percentage = $recommendedAmount > 0 ? ($actualAmount / $recommendedAmount) * 100 : 0;
                        $colorClass = $percentage > 100 ? 'bg-red-500' : 'bg-blue-500';
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400 capitalize">
                                {{ $type }} ({{ ['básico' => '50%', 'lujo' => '20%', 'ahorro' => '30%'][$type] }})
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Gastado: {{ number_format($actualAmount, 0, ',', '.') }} / Rec: {{ number_format($recommendedAmount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="{{ $colorClass }} h-2.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        @if ($percentage > 100)
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                ¡Has excedido el presupuesto en esta categoría!
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
