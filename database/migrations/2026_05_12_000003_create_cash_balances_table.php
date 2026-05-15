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
        Schema::create('cash_balances', function (Blueprint $table) {
            $table->id();
            $table->date('balance_date')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('company')->index();
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->decimal('custody_assets_amount', 15, 2)->default(0);
            $table->decimal('own_assets_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['balance_date', 'sort_order']);
        });

        $now = now();
        $companies = [
            'КА ПКБ',
            'КА Nova Collection',
            'КА iCollect',
            'КА F collect',
            'ТОО RA Holding',
            'ТОО Leader Build',
            'ТОО ONK invest',
            'ТОО QR C',
            'ТОО FO',
            'ТОО VL',
            'ТОО Ra paper',
            'ТОО LD',
            'ТОО RA Transport',
            'ТОО RA const. E.',
            'ТОО СФК NFS',
            'ТОО СФК ALEM Finance',
            'ТОО СФК AN Capital',
            'ТОО СФК Almaty Finance',
            'ТОО BM Beauty Luxe',
            'ИП Көк-Төбе',
            'ИП Набиев Исмаил',
            'ИП АБИБОВ КЕМРАН',
            'ИП Мирзаева',
            'ИП ЦУР',
            'АХО',
            'Касса ИП',
            'Депозит Аббас',
            'Касса нал. BM BL',
            'Нариман Каспи',
        ];

        foreach ($companies as $index => $company) {
            DB::table('cash_balances')->insert([
                'balance_date' => '2026-04-30',
                'sort_order' => $index + 1,
                'company' => $company,
                'balance_amount' => 0,
                'custody_assets_amount' => 0,
                'own_assets_amount' => 0,
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
        Schema::dropIfExists('cash_balances');
    }
};
