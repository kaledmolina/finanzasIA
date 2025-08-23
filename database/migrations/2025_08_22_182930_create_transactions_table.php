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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario que crea la transacción
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Relación con la categoría
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            // 'ingreso' o 'gasto'
            $table->enum('type', ['ingreso', 'gasto']);
            // El tipo de gasto según tu regla (50/20/30). Es anulable porque no aplica a ingresos.
            $table->enum('expense_type', ['básico', 'lujo', 'ahorro'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
