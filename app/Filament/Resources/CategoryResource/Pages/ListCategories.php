<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Category;
use Database\Seeders\SuggestedCategorySeeder;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // Acción para mostrar y seleccionar categorías sugeridas
            Actions\Action::make('suggestCategories')
                ->label('Ver Sugerencias')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->form(function () {
                    // 1. Obtener todas las sugerencias
                    $allSuggestions = SuggestedCategorySeeder::getSuggestions();

                    // 2. Obtener los nombres de las categorías que el usuario ya tiene
                    $userCategoryNames = Category::where('user_id', auth()->id())->pluck('name')->toArray();

                    // 3. Filtrar las sugerencias para no mostrar las que ya existen
                    $filteredSuggestions = array_filter(
                        $allSuggestions,
                        fn ($suggestion) => !in_array($suggestion['name'], $userCategoryNames)
                    );

                    // 4. Formatear para el CheckboxList
                    $options = [];
                    foreach ($filteredSuggestions as $suggestion) {
                        $label = "{$suggestion['name']} (Tipo: {$suggestion['type']}";
                        if ($suggestion['expense_type']) {
                            $label .= ", Gasto: {$suggestion['expense_type']}";
                        }
                        $label .= ")";
                        $options[$suggestion['name']] = $label;
                    }

                    // Si no hay sugerencias nuevas, mostrar un mensaje
                    if (empty($options)) {
                        return [
                            Forms\Components\Placeholder::make('no_suggestions')
                                ->label('¡Todo al día!')
                                ->content('Parece que ya tienes todas nuestras categorías sugeridas.'),
                        ];
                    }

                    return [
                        Forms\Components\CheckboxList::make('categories_to_add')
                            ->label('Selecciona las categorías que deseas añadir a tu cuenta')
                            ->options($options)
                            ->columns(2)
                            ->required(),
                    ];
                })
                ->action(function (array $data) {
                    if (empty($data['categories_to_add'])) {
                        return;
                    }

                    $allSuggestions = collect(SuggestedCategorySeeder::getSuggestions())->keyBy('name');
                    $selectedNames = $data['categories_to_add'];
                    
                    foreach ($selectedNames as $name) {
                        if ($suggestion = $allSuggestions->get($name)) {
                            Category::create(array_merge($suggestion, ['user_id' => auth()->id()]));
                        }
                    }

                    Notification::make()
                        ->title('Categorías añadidas exitosamente')
                        ->success()
                        ->send();
                })
                ->modalHeading('Sugerencias de Categorías')
                ->modalWidth('3xl'),
        ];
    }
}
