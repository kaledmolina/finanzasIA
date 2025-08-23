<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Añadir el tipo de gasto a la tabla de categorías
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('expense_type', ['básico', 'lujo', 'ahorro'])
                  ->nullable() // Anulable porque solo aplica a categorías de tipo 'gasto'
                  ->after('type');
        });

        // 2. Hacer que el ID de categoría sea opcional en las transacciones
        Schema::table('transactions', function (Blueprint $table) {
            // Se necesita cambiar el tipo de columna para hacerla anulable en SQLite
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('expense_type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
