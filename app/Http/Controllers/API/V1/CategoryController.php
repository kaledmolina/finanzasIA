<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = $request->user()->categories()->get();
        return CategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ingreso,gasto',
            'expense_type' => 'nullable|in:básico,lujo,ahorro',
        ]);

        $category = $request->user()->categories()->create($validated);

        return new CategoryResource($category);
    }
    
    // ... (Los métodos show, update, destroy se pueden implementar de forma similar)
}