<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BudgetWidget extends Widget
{
    protected static string $view = 'filament.widgets.budget-widget';

    protected int | string | array $columnSpan = 'full';

    public array $data = [];

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

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $userId = auth()->id();
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        // 1. Ingresos totales del mes
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'ingreso')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // 2. Gastos totales del mes por tipo
        $expensesByType = Transaction::where('user_id', $userId)
            ->where('type', 'gasto')
            ->whereBetween('date', [$startDate, $endDate])
            ->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->pluck('total', 'expense_type');

        $totalExpenses = $expensesByType->sum();

        // 3. Presupuesto recomendado basado en ingresos
        $recommended = [
            'básico' => $totalIncome * 0.50,
            'lujo' => $totalIncome * 0.20,
            'ahorro' => $totalIncome * 0.30,
        ];

        // 4. Gastos actuales
        $actual = [
            'básico' => $expensesByType->get('básico', 0),
            'lujo' => $expensesByType->get('lujo', 0),
            'ahorro' => $expensesByType->get('ahorro', 0),
        ];

        $this->data = [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balance' => $totalIncome - $totalExpenses,
            'recommended' => $recommended,
            'actual' => $actual,
        ];
    }
}
