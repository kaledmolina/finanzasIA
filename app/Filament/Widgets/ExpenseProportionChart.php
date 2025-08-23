<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseProportionChart extends ChartWidget
{
    protected static ?string $heading = 'Tendencia de Gastos Diarios del Mes';
    

    protected static ?int $sort = 2; // Orden en el dashboard
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
        // 1. Obtener los gastos del mes agrupados por día
        $expenses = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'gasto')
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->select(DB::raw('DATE(date) as transaction_date'), DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->pluck('total', 'transaction_date');

        // 2. Preparar los datos para el gráfico, asegurando todos los días del mes
        $labels = [];
        $values = [];
        $daysInMonth = Carbon::now()->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::now()->startOfMonth()->addDays($day - 1)->format('Y-m-d');
            // La etiqueta será solo el número del día (ej: 1, 2, 3...)
            $labels[] = $day;
            // Obtenemos el gasto para ese día, o 0 si no hubo
            $values[] = $expenses->get($date, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gastos Diarios',
                    'data' => $values,
                    'borderColor' => '#f1c40f', // Color amarillo para la línea
                    'tension' => 0.1, // Para suavizar la línea
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
