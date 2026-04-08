<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->date('expense_date');
            $table->enum('split_type', ['equal', 'exact', 'percentage'])->default('equal');
            $table->text('notes')->nullable();
            $table->string('receipt_image')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_expenses');
    }
};
