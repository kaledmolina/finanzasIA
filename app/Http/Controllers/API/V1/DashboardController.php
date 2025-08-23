<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Verificar si hay ingresos este mes
        $hasIncome = $user->transactions()
            ->where('type', 'ingreso')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->exists();

        // Si no hay ingresos, devolver una respuesta simple
        if (!$hasIncome) {
            return response()->json([
                'hasIncome' => false,
                'userName' => $user->name,
            ]);
        }

        // Calcular totales si hay ingresos
        $totalIncome = $user->transactions()
            ->where('type', 'ingreso')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalExpenses = $user->transactions()
            ->where('type', 'gasto')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Obtener las 5 categorías con más gastos
        $topCategories = Transaction::query()
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'gasto')
            ->whereNotNull('transactions.category_id')
            ->whereBetween('transactions.date', [$startOfMonth, $endOfMonth])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'hasIncome' => true,
            'userName' => $user->name,
            'userAvatarUrl' => null, // Puedes añadir una URL de avatar si la tienes
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'balance' => (float) ($totalIncome - $totalExpenses),
            'topCategories' => $topCategories,
        ]);
    }
}
