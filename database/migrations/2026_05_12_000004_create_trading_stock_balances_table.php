<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trading_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->date('balance_date')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('product_group')->index();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['balance_date', 'sort_order']);
        });

        $now = now();
        $groups = [
            'Игрушки деревянные',
            'Игрушки пластиковые',
            'Игрушки пластиковые ',
            'Освещение',
        ];

        foreach ($groups as $index => $group) {
            DB::table('trading_stock_balances')->insert([
                'balance_date' => '2026-04-09',
                'sort_order' => $index + 1,
                'product_group' => $group,
                'quantity' => 0,
                'cost_amount' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_stock_balances');
    }
};
