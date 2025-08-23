<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ExpenseDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Uso del Presupuesto (Regla 50/20/30)';
    
    protected static ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        // Solo se muestra si SÍ hay ingresos este mes
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'ingreso')
            ->whereBetween('date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->exists();
    }

    protected function getData(): array
    {
        // 1. Obtener los ingresos totales del mes para calcular los cupos
        $totalIncome = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'ingreso')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('amount');

        // 2. Obtener los gastos reales del mes por tipo
        $actualExpenses = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'gasto')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->selectRaw('expense_type, SUM(amount) as total')
            ->groupBy('expense_type')
            ->pluck('total', 'expense_type');

        // 3. Definir los porcentajes del presupuesto
        $budgetRules = [
            'básico' => 0.50,
            'lujo' => 0.20,
            'ahorro' => 0.30,
        ];

        $labels = [];
        $usedPercentageData = [];
        $availablePercentageData = [];

        // 4. Calcular el uso y la disponibilidad para cada categoría
        foreach ($budgetRules as $type => $percentage) {
            $budgetLimit = $totalIncome * $percentage;
            $spentAmount = $actualExpenses->get($type, 0);

            // Calcular el porcentaje de uso (sin exceder el 100% para la visualización)
            $usedPercentage = ($budgetLimit > 0) ? ($spentAmount / $budgetLimit) * 100 : 0;
            $clampedUsed = min($usedPercentage, 100);

            $labels[] = ucfirst($type) . ' (' . ($percentage * 100) . '%)';
            $usedPercentageData[] = round($clampedUsed, 2);
            $availablePercentageData[] = round(100 - $clampedUsed, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Usado (%)',
                    'data' => $usedPercentageData,
                    'backgroundColor' => '#3498db', // Azul
                ],
                [
                    'label' => 'Disponible (%)',
                    'data' => $availablePercentageData,
                    'backgroundColor' => '#e0e0e0', // Gris claro
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // Un gráfico de barras es mejor para esta comparación
        return 'bar';
    }
    

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Hace el gráfico horizontal para mejor lectura
            'scales' => [
                'x' => [
                    'stacked' => true, // Apila las barras
                    'max' => 100, // El eje X va de 0 a 100%
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
