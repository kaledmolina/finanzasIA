<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class IncomeReminderWidget extends Widget
{
    protected static string $view = 'filament.widgets.income-reminder-widget';

    // Ocultar el encabezado del widget para un diseño más limpio
    protected static bool $isDiscovered = false;

    /**
     * Este método controla si el widget debe ser visible.
     * Solo se mostrará si el usuario NO ha registrado ingresos este mes.
     */
    public static function canView(): bool
    {
        $hasIncomeThisMonth = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'ingreso')
            ->whereBetween('date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->exists();

        // Devuelve 'true' (mostrar widget) si NO hay ingresos.
        return !$hasIncomeThisMonth;
    }
}
