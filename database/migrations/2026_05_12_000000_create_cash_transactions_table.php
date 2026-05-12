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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date')->index();
            $table->decimal('income_amount', 15, 2)->default(0);
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->string('company')->nullable()->index();
            $table->string('cash_flow')->nullable()->index();
            $table->boolean('has_supporting_document')->nullable()->index();
            $table->timestamps();

            $table->index(['transaction_date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
