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
        Schema::create('rap_report_entries', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->string('section')->index();
            $table->string('counterparty')->nullable()->index();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('sale_amount', 15, 2)->default(0);
            $table->string('sale_month')->nullable()->index();
            $table->date('invoice_date')->nullable()->index();
            $table->date('actual_payment_date')->nullable()->index();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('planned_payment_date')->nullable()->index();
            $table->decimal('unpaid_amount', 15, 2)->default(0);
            $table->boolean('is_paid')->nullable()->index();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['report_date', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rap_report_entries');
    }
};
