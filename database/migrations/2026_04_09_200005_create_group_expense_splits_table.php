<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_settled')->default(false);
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_settled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_expense_splits');
    }
};
