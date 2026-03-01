<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('expense_tag', function (Blueprint $table) {
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['expense_id', 'tag_id']);
        });

        Schema::create('expense_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('old_values');
            $table->json('new_values');
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_histories');
        Schema::dropIfExists('expense_tag');
        Schema::dropIfExists('tags');
    }
};
