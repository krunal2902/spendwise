<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['budget_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_budgets');
    }
};
