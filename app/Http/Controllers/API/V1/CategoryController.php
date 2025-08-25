<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Database\Seeders\SuggestedCategorySeeder; // Importa el seeder
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
            'type' => ['required', Rule::in(['ingreso', 'gasto'])],
            'expense_type' => ['nullable', Rule::in(['básico', 'lujo', 'ahorro'])],
        ]);

        if ($validated['type'] === 'ingreso') {
            $validated['expense_type'] = null;
        }

        $category = $request->user()->categories()->create($validated);
        return response()->json(new CategoryResource($category), 201);
    }

    // --- NUEVO MÉTODO PARA OBTENER SUGERENCIAS ---
    public function getSuggestions(Request $request)
    {
        $allSuggestions = SuggestedCategorySeeder::getSuggestions();
        $userCategoryNames = $request->user()->categories()->pluck('name')->toArray();

        // Filtrar para no mostrar las categorías que el usuario ya tiene
        $filteredSuggestions = array_filter($allSuggestions, function ($suggestion) use ($userCategoryNames) {
            return !in_array($suggestion['name'], $userCategoryNames);
        });

        return response()->json(array_values($filteredSuggestions)); // Re-indexar el array
    }

    // --- NUEVO MÉTODO PARA GUARDAR LAS SUGERENCIAS SELECCIONADAS ---
    public function storeSuggestions(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.name' => 'required|string',
            'categories.*.type' => 'required|string',
            'categories.*.expense_type' => 'nullable|string',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($validated, $user) {
            foreach ($validated['categories'] as $categoryData) {
                $user->categories()->firstOrCreate(
                    ['name' => $categoryData['name']],
                    $categoryData
                );
            }
        });

        return response()->json(['message' => 'Sugerencias guardadas exitosamente'], 201);
    }
}
