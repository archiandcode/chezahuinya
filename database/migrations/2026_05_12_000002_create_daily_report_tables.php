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
        Schema::create('report_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name')->nullable();
            $table->string('category')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('report_company_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_company_id')->constrained('report_companies')->cascadeOnDelete();
            $table->string('account_number');
            $table->string('bank')->nullable();
            $table->timestamps();

            $table->unique(['report_company_id', 'account_number']);
        });

        Schema::create('daily_report_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('direction')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['name', 'direction']);
        });

        Schema::create('daily_report_entries', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->foreignId('report_company_id')->constrained('report_companies')->restrictOnDelete();
            $table->foreignId('report_company_account_id')->nullable()->constrained('report_company_accounts')->nullOnDelete();
            $table->foreignId('daily_report_type_id')->constrained('daily_report_types')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('counterparty')->nullable()->index();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['report_date', 'report_company_id']);
        });

        $now = now();

        $companies = [
            ['name' => 'ТОО КА «ПКБ»', 'short_name' => 'КА ПКБ', 'category' => 'Коллекторское агентство'],
            ['name' => 'ТОО «F C»', 'short_name' => 'КА FC', 'category' => 'Коллекторское агентство'],
            ['name' => 'ТОО «iC»', 'short_name' => 'КА iC', 'category' => 'Коллекторское агентство'],
            ['name' => 'ТОО «N C»', 'short_name' => null, 'category' => 'Коллекторское агентство'],
            ['name' => 'ТОО «RA Holding»', 'short_name' => null, 'category' => 'Инвест.компани'],
            ['name' => 'ТОО «RA paper»', 'short_name' => null, 'category' => 'Производство'],
            ['name' => 'New Finance Sol.', 'short_name' => 'СФК NFS', 'category' => 'Финансовые компании'],
            ['name' => 'ALEM Finance', 'short_name' => 'СФК ALEM Finance', 'category' => 'Финансовые компании'],
            ['name' => 'AN Capital', 'short_name' => null, 'category' => 'Финансовые компании'],
            ['name' => 'Almaty Finance', 'short_name' => 'СФК Almaty Finance', 'category' => 'Финансовые компании'],
            ['name' => 'ТОО «V L»', 'short_name' => null, 'category' => 'Обслуживающие компании'],
            ['name' => 'ТОО «QR C»', 'short_name' => null, 'category' => 'Обслуживающие компании'],
            ['name' => 'ТОО «FaxOm»', 'short_name' => null, 'category' => 'Обслуживающие компании'],
            ['name' => 'ИП ЦУР', 'short_name' => null, 'category' => 'Обслуживающие компании'],
            ['name' => 'ИП «Көк-Төбе»', 'short_name' => null, 'category' => 'Аренда'],
            ['name' => 'ТОО «RA Constr E»', 'short_name' => null, 'category' => 'Строительные компании'],
            ['name' => 'ТОО "ONK invest"', 'short_name' => null, 'category' => 'Строительные компании'],
            ['name' => 'ТОО «Leader Build»', 'short_name' => null, 'category' => 'Строительные компании'],
            ['name' => 'ИП Абибов Кемран', 'short_name' => null, 'category' => 'Транспортные'],
            ['name' => 'ИП Набиев Исмаил', 'short_name' => null, 'category' => 'Транспортные'],
            ['name' => 'ТОО "LD"', 'short_name' => null, 'category' => 'Торговые компании'],
            ['name' => 'ТОО «RA Transport»', 'short_name' => null, 'category' => 'Торговые компании'],
            ['name' => 'ТОО BM Beаuty Luxe', 'short_name' => null, 'category' => 'Салон красоты'],
            ['name' => 'ИП Мирзаева', 'short_name' => null, 'category' => 'Салон красоты'],
        ];

        foreach ($companies as $company) {
            DB::table('report_companies')->insert($company + ['created_at' => $now, 'updated_at' => $now]);
        }

        $companyIds = DB::table('report_companies')->pluck('id', 'name');
        $accounts = [
            'ТОО КА «ПКБ»' => [
                'KZ329650200007871941',
                'KZ6296502F0010351153',
                'KZ3796502F0011645777',
                'KZ9796502F0012278107',
                'KZ58998CTB0000682742 (JUSAN)',
                'KZ86601A861003469691 (Народный)',
                'KZ9494806KZT22036599 (Евразийский)',
            ],
            'ТОО «F C»' => [
                'KZ5496502F0017239752',
                'KZ7396502F0017745750',
                'KZ53998CTB0001741490 (JUSAN)',
                'KZ94601A861035235301 (Народный)',
                'KZ5994806KZT22038781 (Евразийский)',
            ],
            'ТОО «iC»' => [
                'KZ6896502F0015023050',
                'KZ3996502F0018314782',
                'KZ45998CTB0001768988 (JUSAN)',
                'KZ37601A861042925711 (Народный)',
                'KZ9494806KZT22038927 (Евразийский)',
            ],
            'New Finance Sol.' => [
                'KZ2796502F0021037109',
                'KZ2194800KZT22020092 (кастоди)',
            ],
            'ALEM Finance' => [
                'KZ1396502F0020929982',
                'KZ2694800KZT22020099 (кастоди)',
            ],
            'Almaty Finance' => [
                'KZ5496502F0021570996',
                'KZ23998CTB0001895290 (JUSAN)',
                'KZ6994800KZT22020101 (кастоди)',
            ],
        ];

        foreach ($accounts as $companyName => $companyAccounts) {
            foreach ($companyAccounts as $account) {
                DB::table('report_company_accounts')->insert([
                    'report_company_id' => $companyIds[$companyName],
                    'account_number' => trim($account),
                    'bank' => $this->bankFromAccount($account),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $types = [
            ['name' => 'на начало дня', 'direction' => 'opening'],
            ['name' => 'взыскание', 'direction' => 'income'],
            ['name' => 'приход от деятельности', 'direction' => 'income'],
            ['name' => 'возврат взысканной суммы', 'direction' => 'income'],
            ['name' => 'по тендерам', 'direction' => 'income'],
            ['name' => 'приход м/у счетами ТОО и ИП', 'direction' => 'income'],
            ['name' => 'ПКБ', 'direction' => 'income'],
            ['name' => 'приход м/у счетами внутри', 'direction' => 'income'],
            ['name' => 'касса', 'direction' => 'income'],
            ['name' => 'конвертация', 'direction' => 'income'],
            ['name' => 'возврат поставщиков', 'direction' => 'income'],
            ['name' => 'возврат по письму', 'direction' => 'income'],
            ['name' => 'возврат налогов', 'direction' => 'income'],
            ['name' => 'возврат налогов/зп', 'direction' => 'income'],
            ['name' => 'возврат', 'direction' => 'income'],
            ['name' => 'расход м/у счетами ТОО и ИП', 'direction' => 'expense'],
            ['name' => 'F collect', 'direction' => 'expense'],
            ['name' => 'ИП "Көк-Төбе"', 'direction' => 'expense'],
            ['name' => 'СФК NFS', 'direction' => 'expense'],
            ['name' => 'расход м/у счетами внутри', 'direction' => 'expense'],
            ['name' => 'расход м/у счетами', 'direction' => 'expense'],
            ['name' => 'за товар', 'direction' => 'expense'],
            ['name' => 'за услуги', 'direction' => 'expense'],
            ['name' => 'текущие платежи', 'direction' => 'expense'],
            ['name' => 'налоги', 'direction' => 'expense'],
            ['name' => 'зп/аванс/увольнение/отпуск', 'direction' => 'expense'],
            ['name' => 'Инкасса в кассу', 'direction' => 'expense'],
            ['name' => 'инкасса в кассу', 'direction' => 'expense'],
            ['name' => 'для пополнения счета', 'direction' => 'expense'],
            ['name' => 'комиссия банка', 'direction' => 'expense'],
        ];

        foreach ($types as $index => $type) {
            DB::table('daily_report_types')->insert($type + [
                'sort_order' => $index + 1,
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
        Schema::dropIfExists('daily_report_entries');
        Schema::dropIfExists('daily_report_types');
        Schema::dropIfExists('report_company_accounts');
        Schema::dropIfExists('report_companies');
    }

    private function bankFromAccount(string $account): ?string
    {
        return match (true) {
            str_contains($account, 'JUSAN') => 'JUSAN',
            str_contains($account, 'Народный') => 'Народный',
            str_contains($account, 'Евразийский') => 'Евразийский',
            str_contains($account, 'кастоди') => 'Кастоди',
            default => null,
        };
    }
};
