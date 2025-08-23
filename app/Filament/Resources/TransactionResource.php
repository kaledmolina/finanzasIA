<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Transacción';
    protected static ?string $pluralModelLabel = 'Transacciones';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo de Transacción')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'gasto' => 'Gasto',
                    ])
                    ->required()
                    ->live() 
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('category_id', null)), 

                Forms\Components\Select::make('category_id')
                    ->label('Categoría (Opcional)')
                    ->options(function (Get $get): Collection {
                        $type = $get('type');
                        if (!$type) {
                            return collect();
                        }
                        return Category::where('user_id', auth()->id())
                                       ->where('type', $type)
                                       ->pluck('name', 'id');
                    })
                    ->live(), // <-- Añadir live() para reactividad

                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->prefix('COP'),

                Forms\Components\TextInput::make('description')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255),

                // Este campo solo aparecerá si es un gasto SIN categoría
                Forms\Components\Select::make('expense_type')
                    ->label('Tipo de Gasto (Regla 50/30/20)')
                    ->options([
                        'básico' => 'Básico (50%)',
                        'lujo' => 'Lujo/Deseo (20%)',
                        'ahorro' => 'Ahorro/Inversión (30%)',
                    ])
                    ->required()
                    ->visible(function (Get $get): bool {
                        // Mostrar solo si es un gasto Y no se ha seleccionado una categoría.
                        return $get('type') === 'gasto' && empty($get('category_id'));
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Fecha')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Descripción')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Monto')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Categoría')->badge()->placeholder('Sin categoría'),
                Tables\Columns\TextColumn::make('type')->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'gasto' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('expense_type')->label('Tipo de Gasto')->badge()->color('warning'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
