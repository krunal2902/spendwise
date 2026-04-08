<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('paid_to')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamp('settled_at')->useCurrent();
            $table->timestamps();

            $table->index(['group_id', 'settled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_settlements');
    }
};
