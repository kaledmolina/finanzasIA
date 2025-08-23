<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopExpenseCategories extends BaseWidget
{
    protected static ?int $sort = 3; // Orden en el dashboard
    protected int | string | array $columnSpan = 'full';

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

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Consulta para obtener las categorías con mayor gasto en el mes actual
                Transaction::query()
                    ->where('transactions.user_id', auth()->id())
                    ->where('transactions.type', 'gasto')
                    ->whereNotNull('transactions.category_id') // Solo transacciones con categoría
                    ->whereBetween('transactions.date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->join('categories', 'transactions.category_id', '=', 'categories.id')
                    ->select(
                        'categories.id', // Seleccionar el ID para usarlo como clave única
                        'categories.name as category_name',
                        DB::raw('SUM(transactions.amount) as total_amount')
                    )
                    ->groupBy('categories.id', 'categories.name') // Agrupar también por el ID único
                    ->orderByDesc('total_amount')
                    ->limit(5)
            )
            ->heading('Top 5 Categorías de Gasto del Mes')
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Categoría'),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Gastado')
                    ->money('COP'),
            ])
            ->paginated(false); // No necesitamos paginación para solo 5 items
    }
}