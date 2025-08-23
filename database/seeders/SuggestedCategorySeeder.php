<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuggestedCategorySeeder extends Seeder
{
    /**
     * Devuelve una lista de categorías sugeridas.
     *
     * @return array
     */
    public static function getSuggestions(): array
    {
        return [
            // Ingresos
            ['name' => 'Salario', 'type' => 'ingreso', 'expense_type' => null],
            ['name' => 'Bonos', 'type' => 'ingreso', 'expense_type' => null],
            ['name' => 'Ingreso Extra', 'type' => 'ingreso', 'expense_type' => null],

            // Gastos Básicos (50%)
            ['name' => 'Mercado', 'type' => 'gasto', 'expense_type' => 'básico'],
            ['name' => 'Arriendo / Hipoteca', 'type' => 'gasto', 'expense_type' => 'básico'],
            ['name' => 'Servicios Públicos', 'type' => 'gasto', 'expense_type' => 'básico'],
            ['name' => 'Transporte', 'type' => 'gasto', 'expense_type' => 'básico'],
            ['name' => 'Salud', 'type' => 'gasto', 'expense_type' => 'básico'],

            // Gastos de Lujo/Deseos (20%)
            ['name' => 'Restaurantes', 'type' => 'gasto', 'expense_type' => 'lujo'],
            ['name' => 'Entretenimiento', 'type' => 'gasto', 'expense_type' => 'lujo'],
            ['name' => 'Compras (Ropa, etc.)', 'type' => 'gasto', 'expense_type' => 'lujo'],
            ['name' => 'Viajes', 'type' => 'gasto', 'expense_type' => 'lujo'],

            // Ahorro / Inversión (30%)
            ['name' => 'Ahorro a Largo Plazo', 'type' => 'gasto', 'expense_type' => 'ahorro'],
            ['name' => 'Inversiones', 'type' => 'gasto', 'expense_type' => 'ahorro'],
            ['name' => 'Pago de Deudas', 'type' => 'gasto', 'expense_type' => 'ahorro'],
        ];
    }

    /**
     * Ejecuta el seeder para un usuario específico.
     * (Este método ya no se usa directamente desde el botón, pero es útil mantenerlo)
     *
     * @param int $userId
     * @return void
     */
    public function run(int $userId): void
    {
        $suggestions = self::getSuggestions();

        DB::transaction(function () use ($userId, $suggestions) {
            foreach ($suggestions as $category) {
                Category::firstOrCreate(
                    ['user_id' => $userId, 'name' => $category['name']],
                    $category
                );
            }
        });
    }
}
