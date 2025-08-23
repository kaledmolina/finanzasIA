<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Category;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el ID del usuario
        $data['user_id'] = auth()->id();

        // Si se seleccionó una categoría, obtener su tipo de gasto
        if (!empty($data['category_id'])) {
            $category = Category::find($data['category_id']);
            $data['expense_type'] = $category?->expense_type;
        } 
        // Si no hay categoría y es un ingreso, asegurar que sea null
        elseif ($data['type'] === 'ingreso') {
            $data['expense_type'] = null;
        }
        // Si no hay categoría y es un gasto, el valor ya viene del formulario.
 
        return $data;
    }
}
