<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = $request->user()->transactions()->with('category')->latest('date')->get();
        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'type' => 'required|in:ingreso,gasto',
            'category_id' => 'nullable|exists:categories,id',
            'expense_type' => 'nullable|in:básico,lujo,ahorro',
        ]);

        $transaction = $request->user()->transactions()->create($validated);

        return new TransactionResource($transaction);
    }
    
    // ... (Los métodos show, update, destroy se pueden implementar de forma similar)
}
