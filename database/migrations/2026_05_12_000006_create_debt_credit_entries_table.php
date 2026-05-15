<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_credit_entries', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->string('section')->index();
            $table->string('group_name')->nullable()->index();
            $table->string('counterparty')->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('company')->nullable()->index();
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['report_date', 'section', 'sort_order']);
        });

        $now = now();
        $entries = [
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'ФОРТЕ Банк', 'company' => 'Омаров Аббас', 'note' => 'ЗУ Жулдыз', 'sort_order' => 5],
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'ФОРТЕ Банк', 'company' => 'Омаров Аббас', 'note' => 'ЗУ Каскелен', 'sort_order' => 6],
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'ФОРТЕ Банк', 'company' => 'Омаров Аббас', 'note' => 'ЗУ Жулдыз налог на землю', 'sort_order' => 7],
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'ФОРТЕ Банк', 'company' => 'Омаров Аббас', 'note' => 'ЗУ Каскелен налог на землю', 'sort_order' => 8],
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'Ибрагимов К У', 'company' => 'ТОО ONK invest', 'note' => 'ЗУ Шымкент без нал', 'sort_order' => 9],
            ['section' => 'creditor', 'group_name' => 'Кредитование-финансирование активы', 'counterparty' => 'Ибрагимов К У', 'company' => 'ТОО ONK invest', 'note' => 'ЗУ Шымкент нал', 'sort_order' => 10],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / БВУ:', 'counterparty' => 'АО «First Heartland Jusan Bank»', 'company' => 'ТОО "КА ПКБ"', 'note' => 'Цессия', 'sort_order' => 13],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / БВУ:', 'counterparty' => 'АО «Halyk Bank»', 'company' => 'СФК NFS', 'note' => 'Цессия пул1', 'sort_order' => 14],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / БВУ:', 'counterparty' => 'АО «ABC»', 'company' => 'СФК AlemF', 'note' => 'Цессия пул2', 'sort_order' => 15],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / БВУ:', 'counterparty' => 'АО «ABC»', 'company' => 'СФК AlmatyF', 'note' => 'Цессия пул3', 'sort_order' => 16],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / КА:', 'counterparty' => 'ТОО "КА ПКБ"', 'company' => 'ТОО "КА FC"', 'note' => 'Цессия', 'sort_order' => 18],
            ['section' => 'creditor', 'group_name' => 'Договора Цессии по графику / КА:', 'counterparty' => 'ТОО "КА ПКБ"', 'company' => 'ТОО "КА IC"', 'note' => 'Цессия', 'sort_order' => 19],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'Абибова Мадина Махамадовна', 'company' => 'ТОО "RACE"', 'note' => 'ВФП номинально', 'sort_order' => 21],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'Омарова Гульжохра Алияровна', 'company' => 'ТОО "RA corp."', 'note' => 'ВФП номинально', 'sort_order' => 22],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'Набиев Расул Александрович', 'company' => 'ТОО "LUXURY DISTRIBUTION"', 'note' => 'ВФП номинально', 'sort_order' => 23],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'Абибова Мадина Махаммадовна', 'company' => 'ТОО «RA paper»', 'note' => 'ВФП аннулирован в Базе', 'sort_order' => 24],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'Омаров Нариман', 'company' => 'ТОО «QR Consalting»', 'note' => 'ВФП', 'sort_order' => 25],
            ['section' => 'creditor', 'group_name' => 'ВФП номинально в кассу и на р/с факт', 'counterparty' => 'ТОО «RA Holding»', 'company' => 'ТОО Leader Build', 'note' => 'ВФП', 'sort_order' => 26],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Раджабов Руслан Сахрабович', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ по графику', 'sort_order' => 32],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Асанов Өркен Сұлтанұлы', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ решение суда', 'sort_order' => 33],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Убитаев Еркебулан Нурланович', 'company' => 'ТОО "RACE"', 'note' => 'займ +аванс', 'sort_order' => 34],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Абибова Зарина', 'company' => 'Касса ИП', 'note' => 'займ, возврат разовый', 'sort_order' => 35],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Фахратов Омар', 'company' => 'ТОО «RA paper»', 'note' => 'займ по графику', 'sort_order' => 36],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Аббасов Ибрагим', 'company' => 'ТОО "RA corp."', 'note' => 'займ по графику', 'sort_order' => 37],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Елисеева Елена', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ по графику ремонт авто', 'sort_order' => 38],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Коныратбаев Ержан', 'company' => 'Касса ИП', 'note' => 'расрочка авто должника', 'sort_order' => 39],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Тугулева Асель', 'company' => 'ТОО "VL"', 'note' => 'займ по графику', 'sort_order' => 40],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Чепрасова Ольга', 'company' => 'ТОО "VL"', 'note' => 'займ по графику', 'sort_order' => 41],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Құлмахан Саят Жарасұлы', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ по графику', 'sort_order' => 42],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Шакирова Ажар', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ с зп май', 'sort_order' => 43],
            ['section' => 'debtor', 'group_name' => 'Займы сотрудников по графику', 'counterparty' => 'Тоғайбаева Жангүл', 'company' => 'ТОО "КА ПКБ"', 'note' => 'займ по графику', 'sort_order' => 44],
            ['section' => 'debtor', 'group_name' => 'Займы третьи лица', 'counterparty' => 'Шариф', 'company' => 'Касса ИП', 'note' => 'займ на 2 месяца (взят в январе 25)', 'sort_order' => 46],
            ['section' => 'debtor', 'group_name' => 'Займы третьи лица', 'counterparty' => 'Мамадалиева Г.Г.', 'company' => 'Касса ИП', 'note' => 'займ на 2 года (взят 26 мая 25)', 'sort_order' => 47],
            ['section' => 'debtor', 'group_name' => 'Займы третьи лица', 'counterparty' => 'Х', 'company' => 'Касса ИП инкас Аббасу', 'note' => 'займ до 10/01/2026 г', 'sort_order' => 48],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'ТОО Leader Build', 'company' => 'ТОО «RA Holding»', 'note' => 'ЗУ и дом', 'sort_order' => 50],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'ТОО "RACE"', 'company' => 'ТОО "КА ПКБ"', 'note' => 'дкп авто', 'sort_order' => 51],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'ТОО «RA Holding»', 'company' => 'ТОО "КА ПКБ"', 'note' => 'дкп авто', 'sort_order' => 52],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'Ершуманов Жанат Оракович', 'company' => 'ТОО "КА ПКБ"', 'note' => 'за квартиру', 'sort_order' => 53],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'ИП "САХЫМБЕТОВ"', 'company' => 'ТОО "КА ПКБ"', 'note' => 'за мебель', 'sort_order' => 54],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'ИП "САХЫМБЕТОВ"', 'company' => 'ТОО "КА IC"', 'note' => 'за мебель', 'sort_order' => 55],
            ['section' => 'debtor', 'group_name' => 'Расчеты по имуществу', 'counterparty' => 'Ахмедова Альвина Зльфихаровна', 'company' => 'ТОО "КА ПКБ"', 'note' => 'Эльдар Кобальт 566 GK 02', 'sort_order' => 56],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Раджабов Руслан Сахрабович', 'company' => 'ТОО "КА ПКБ"', 'note' => 'по делам ТОО', 'sort_order' => 58],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Абибов Рустам Алиярович', 'company' => 'ТОО "КА ПКБ"', 'note' => 'по делам ТОО', 'sort_order' => 59],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'ТОО «RA Holding»', 'company' => 'ТОО "КА ПКБ"', 'note' => 'Займ на финансирование', 'sort_order' => 60],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Фахратов Омар', 'company' => 'ТОО "Vegas Lex (Вегас Лекс)"', 'note' => 'по делам ТОО', 'sort_order' => 61],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Шаймахан Жансая Аббасқызы', 'company' => 'ТОО "КА FC"', 'note' => 'по делам ТОО', 'sort_order' => 62],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Ерканат', 'company' => 'ТОО "КА FC"', 'note' => 'дивиденды', 'sort_order' => 63],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Омаров Аббас', 'company' => 'ТОО "КА FC"', 'note' => 'по делам ТОО', 'sort_order' => 64],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'ТОО «RA paper»', 'company' => 'ТОО «RA Holding»', 'note' => 'Займ', 'sort_order' => 65],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'СФК NFS', 'company' => 'ТОО «RA Holding»', 'note' => 'займ', 'sort_order' => 66],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'РА', 'company' => 'ТОО «RA Holding»', 'note' => 'займ', 'sort_order' => 67],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'ТОО ONK invest', 'company' => 'ТОО "КА IC"', 'note' => 'займ', 'sort_order' => 68],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Омаров Нариман', 'company' => 'ТОО "КА IC"', 'note' => 'займ', 'sort_order' => 69],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'ИП Фарида', 'company' => 'ТОО ONK invest', 'note' => 'займ салон', 'sort_order' => 70],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'BM Beauty Luxe', 'company' => 'ТОО ONK invest', 'note' => 'займ салон', 'sort_order' => 71],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'ТОО "КА NC"', 'company' => 'ТОО «RA Holding»', 'note' => 'займ', 'sort_order' => 72],
            ['section' => 'debtor', 'group_name' => 'Займы по делам ТОО', 'counterparty' => 'Байрам З', 'company' => 'ТОО "КА NC"', 'note' => 'займ', 'sort_order' => 73],
            ['section' => 'debtor', 'group_name' => 'по деятельности', 'counterparty' => 'ТОО "VL"', 'company' => 'ТОО "КА IC"', 'note' => 'пред-ские', 'sort_order' => 75],
            ['section' => 'debtor', 'group_name' => 'по деятельности', 'counterparty' => 'ТОО "VL"', 'company' => 'ТОО "КА FC"', 'note' => 'пред-ские', 'sort_order' => 76],
            ['section' => 'debtor', 'group_name' => 'по деятельности', 'counterparty' => 'ТОО "КА ПКБ"', 'company' => 'ТОО "КА NC"', 'note' => 'услуги вз-ние', 'sort_order' => 77],
            ['section' => 'debtor', 'group_name' => 'по деятельности', 'counterparty' => 'ТОО "VL"', 'company' => 'ТОО "КА ПКБ"', 'note' => 'пред-ские', 'sort_order' => 78],
            ['section' => 'debtor', 'group_name' => 'Сотрудники-должники по деятельности', 'counterparty' => 'Фахратов Омар', 'company' => 'РА', 'note' => 'за билеты', 'sort_order' => 80],
            ['section' => 'debtor', 'group_name' => 'Сотрудники-должники по деятельности', 'counterparty' => 'Кабдуллин Ерик Оразканович', 'company' => 'Юсупов Абдулазиз Абдулаевич', 'note' => 'БВУ, Выезд, Е-Нотариат', 'sort_order' => 81],
            ['section' => 'debtor', 'group_name' => 'Сотрудники-должники по деятельности', 'counterparty' => 'Тургинбек Бакдаулет Русланулы', 'company' => 'Абибов Рустам Алиярович', 'note' => 'БВУ, Е-Нотариат', 'sort_order' => 82],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Брандт Алексей Петрович', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 84],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Низамов Вериат Маратович', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 85],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Кожахметов Санжар Маратұлы HYUNDAI ACCENT г/н -661ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 86],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Боласпаев Темірлан Талғатұлы HYUNDAI ACCENT г/н -895ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 87],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Мұхат Шыңғыс Едікұлы HYUNDAI ACCENT г/н -871ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 88],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Исабаев Серик Муратович HYUNDAI ACCENT г/н -895ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 89],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Әділгереев Асылбек Айболатұлы HYUNDAI ACCENT г/н -841ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 90],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Найманбай Султанбек Толегенұлы HYUNDAI ACCENT г/н -864ZC05', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 91],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Гриньков Игорь Лову', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 92],
            ['section' => 'debtor', 'group_name' => 'ZERO  по деятельности', 'counterparty' => 'Мамырқан Нурбатыр Бауыржанұлы', 'company' => 'ТОО «RA paper»', 'note' => 'такси за  авто дтп', 'sort_order' => 93],
            ['section' => 'debtor', 'group_name' => 'решение суда', 'counterparty' => 'ЧН Нотариус Кабдуллин Е.О', 'company' => 'ТОО "КА ПКБ"', 'note' => 'суд нот', 'sort_order' => 95],
            ['section' => 'debtor', 'group_name' => 'решение суда', 'counterparty' => 'ИП Ефименко', 'company' => 'ТОО "КА ПКБ"', 'note' => 'решение суда за TN017', 'sort_order' => 96],
            ['section' => 'debtor', 'group_name' => 'решение суда', 'counterparty' => 'АтласСофт ТОО', 'company' => 'ТОО "QazaQ Go"', 'note' => 'за программу решение суда', 'sort_order' => 97],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'ТОО "КА IC"', 'note' => 'отправка писем пополнение счета', 'sort_order' => 99],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'ТОО "КА FC"', 'note' => 'отправка писем пополнение счета', 'sort_order' => 100],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'ТОО "КА ПКБ"', 'note' => 'отправка писем пополнение счета', 'sort_order' => 101],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'ТОО "VL"', 'note' => 'отправка писем пополнение счета', 'sort_order' => 102],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'СФК NFS', 'note' => 'отправка писем пополнение счета', 'sort_order' => 103],
            ['section' => 'debtor', 'group_name' => 'Казпочта на счету по договору', 'counterparty' => 'АО Казпочта', 'company' => 'СФК ALEM', 'note' => 'отправка писем пополнение счета', 'sort_order' => 104],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Halal_food_kz', 'company' => 'ТОО "LUXURY DISTRIBUTION"', 'note' => 'за товар', 'sort_order' => 106],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Ажара Ш', 'company' => 'ТОО "RA corp."', 'note' => 'игрушки', 'sort_order' => 107],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Рамиль', 'company' => 'ТОО "RA corp."', 'note' => 'игрушки', 'sort_order' => 108],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Гани', 'company' => 'ТОО "RA corp."', 'note' => 'треки', 'sort_order' => 109],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Гани', 'company' => 'ТОО "RA corp."', 'note' => 'стенд', 'sort_order' => 110],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'LAMPA ИП (Гани)', 'company' => 'ТОО "RA corp."', 'note' => 'треки', 'sort_order' => 111],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Обеспечение взнос RAP', 'company' => 'ТОО «RA paper»', 'note' => 'обеспечение взноса', 'sort_order' => 112],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Мутлу Байрам (Салих налоги ЗП январь и февраль)', 'company' => 'ТОО «RA paper»', 'note' => 'за бумагу', 'sort_order' => 113],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Фахратов Омар (долг за бумагу удержем с ЗП апрель май)', 'company' => 'ТОО «RA paper»', 'note' => 'за бумагу', 'sort_order' => 114],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'Аббасов Ибрагим (долг за бумагу удержем с ЗП апрель май)', 'company' => 'ТОО «RA paper»', 'note' => 'за бумагу', 'sort_order' => 115],
            ['section' => 'debtor', 'group_name' => 'Должники по торговым', 'counterparty' => 'тендер', 'company' => 'ТОО «RA paper»', 'note' => 'тендер', 'sort_order' => 116],
        ];

        foreach ($entries as $entry) {
            DB::table('debt_credit_entries')->insert($entry + [
                'report_date' => '2026-04-30',
                'amount' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_credit_entries');
    }
};
