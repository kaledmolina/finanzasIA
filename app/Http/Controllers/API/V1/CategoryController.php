<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Devuelve las categorías del usuario autenticado.
     */
    public function index(Request $request)
    {
        $categories = $request->user()->categories()->get();
        return CategoryResource::collection($categories);
    }

    /**
     * Almacena una nueva categoría.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['ingreso', 'gasto'])],
            'expense_type' => ['nullable', Rule::in(['básico', 'lujo', 'ahorro'])],
        ]);

        // Si es un ingreso, asegurar que expense_type sea null
        if ($validated['type'] === 'ingreso') {
            $validated['expense_type'] = null;
        }

        $category = $request->user()->categories()->create($validated);

        return response()->json(new CategoryResource($category), 201);
    }
}