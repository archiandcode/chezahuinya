<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buffet_report_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('report_year')->index();
            $table->string('period_label')->index();
            $table->date('period_date')->nullable()->index();
            $table->string('metric')->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_year', 'metric', 'sort_order']);
        });

        $now = now();
        $periods = [
            2024 => [
                ['Январь', '2024-01-03'],
                ['Февраль', '2024-02-01'],
                ['Март', '2024-03-01'],
                ['Апрель', '2024-04-01'],
                ['Май', '2024-05-01'],
                ['Июнь', '2024-06-01'],
                ['Июль', '2024-07-01'],
                ['Август', '2024-08-01'],
                ['Сентябрь', '2024-09-01'],
                ['Октябрь', '2024-10-22'],
                ['Ноябрь', '2024-11-01'],
                ['Декабрь', '2024-12-01'],
            ],
            2025 => [
                ['Январь', '2025-01-01'],
                ['Февраль', '2025-02-01'],
                ['Март', '2025-03-01'],
                ['Апрель', '2025-04-30'],
                ['Май', '2025-05-01'],
                ['Июнь', '2025-06-01'],
                ['Июль', '2025-07-01'],
                ['Август', '2025-08-01'],
                ['Сентябрь', '2025-09-01'],
                ['Октябрь', '2025-10-01'],
                ['Ноябрь', '2025-11-01'],
                ['Декабрь', '2025-12-31'],
            ],
            2026 => [
                ['Январь', '2026-01-01'],
                ['01-18.02.2026', null],
                ['25-31.03.2026', null],
                ['01-29.04.2026', null],
            ],
        ];

        $values = [
            2024 => [
                'expense' => [1700990, 1856550, 268565, 1337651, 1896306, 1942080, 2510962, 2300391, 2288875, 2101689, 2077781, 1973693],
                'income' => [1825292, 1991210, 375500, 911900, 1407955, 2396745, 2257620, 2758465, 1985620, 2603459, 2185225, 2497750],
                'total' => [124302, 134660, 106935, -425751, -488351, 454665, -253342, 458074, -303255, 501770, 107444, 524057],
                'representative_expenses' => [210400, 175150, 88650, 57350, 60760, 45700, 51350, 148150, 209440, 342000, 204000, 155440],
                'profit_loss' => [-86098, -40490, 18285, -483101, -549111, 408965, -304692, 309924, -512695, 159770, -96556, 368617],
            ],
            2025 => [
                'expense' => [],
                'income' => [],
                'employee_debt_income' => [],
                'total' => [],
                'representative_expenses' => [],
            ],
            2026 => [
                'expense' => [],
                'income' => [],
                'employee_debt_income' => [],
                'total' => [],
                'representative_expenses' => [],
            ],
        ];

        foreach ($periods as $year => $yearPeriods) {
            foreach ($values[$year] as $metric => $amounts) {
                foreach ($yearPeriods as $index => [$label, $date]) {
                    DB::table('buffet_report_entries')->insert([
                        'report_year' => $year,
                        'period_label' => $label,
                        'period_date' => $date,
                        'metric' => $metric,
                        'amount' => $amounts[$index] ?? 0,
                        'sort_order' => $index + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        foreach (['Деньги в обороте', 'в кассе на 29.04.2026', 'Долг сотрудников', 'Остаток товара', 'Долг поставщикам'] as $index => $metric) {
            DB::table('buffet_report_entries')->insert([
                'report_year' => 2026,
                'period_label' => 'Для информации',
                'period_date' => null,
                'metric' => $metric,
                'amount' => 0,
                'sort_order' => 100 + $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('buffet_report_entries');
    }
};
