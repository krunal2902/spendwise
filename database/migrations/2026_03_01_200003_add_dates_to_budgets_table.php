<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->enum('type', ['monthly', 'custom'])->default('monthly')->after('year');
            $table->date('start_date')->nullable()->after('type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->boolean('carry_forward')->default(false)->after('end_date');
            $table->decimal('carried_amount', 12, 2)->default(0)->after('carry_forward');
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn(['type', 'start_date', 'end_date', 'carry_forward', 'carried_amount']);
        });
    }
};
