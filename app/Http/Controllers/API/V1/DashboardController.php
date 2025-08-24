<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $hasIncome = $user->transactions()
                ->where('type', 'ingreso')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->exists();

            if (!$hasIncome) {
                return response()->json([
                    'hasIncome' => false,
                    'userName' => $user->name,
                ]);
            }

            $totalIncome = $user->transactions()
                ->where('type', 'ingreso')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $totalExpenses = $user->transactions()
                ->where('type', 'gasto')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

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

            // --- NUEVOS DATOS ---
            // 1. Calcular gastos por tipo para el uso del presupuesto
            $budgetUsage = $user->transactions()
                ->where('type', 'gasto')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->select('expense_type', DB::raw('SUM(amount) as total'))
                ->groupBy('expense_type')
                ->pluck('total', 'expense_type');

            // 2. Calcular gastos diarios
            $dailySpending = $user->transactions()
                ->where('type', 'gasto')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');

            return response()->json([
                'hasIncome' => true,
                'userName' => $user->name,
                'userAvatarUrl' => null,
                'totalIncome' => (float) $totalIncome,
                'totalExpenses' => (float) $totalExpenses,
                'balance' => (float) ($totalIncome - $totalExpenses),
                'topCategories' => $topCategories,
                // --- AÑADIR NUEVOS DATOS A LA RESPUESTA ---
                'budgetUsage' => [
                    'needs' => (float) ($budgetUsage['básico'] ?? 0),
                    'wants' => (float) ($budgetUsage['lujo'] ?? 0),
                    'savings' => (float) ($budgetUsage['ahorro'] ?? 0),
                ],
                'dailySpending' => $dailySpending,
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error al obtener los datos del dashboard.'], 500);
        }
    }
}