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
        Schema::create('construction_payments', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->index();
            $table->string('supplier')->nullable()->index();
            $table->decimal('amount', 15, 2);
            $table->string('contract')->nullable()->index();
            $table->text('purpose')->nullable();
            $table->string('payment_source')->nullable()->index();
            $table->timestamps();

            $table->index(['payment_date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construction_payments');
    }
};
