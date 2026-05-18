<?php

namespace Tests\Feature;

use App\Models\BuffetReportEntry;
use App\Models\CashBalance;
use App\Models\DailyReportEntry;
use App\Models\DebtCreditEntry;
use App\Models\RapReportEntry;
use App\Models\TradingStockBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class ReportDynamicViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_pages_show_xlsx_style_dynamic_blocks(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('Свод ежедневного отчета')
            ->assertSee('Тип операции');

        $this->actingAs($user)
            ->get(route('buffet-reports.index'))
            ->assertOk()
            ->assertSee('Свод буфета за '.BuffetReportEntry::query()->max('report_year'))
            ->assertSee('Показатель');

        $this->actingAs($user)
            ->get(route('debt-credit-reports.index'))
            ->assertOk()
            ->assertSee('Свод Дт и Кт по группам')
            ->assertSee('Кредиторская задолженность')
            ->assertSee('Дебиторская задолженность');
    }

    public function test_report_create_forms_use_latest_existing_dates_as_defaults(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('value="'.(DailyReportEntry::query()->max('report_date') ?? now()->toDateString()).'"', false);

        $this->actingAs($user)
            ->get(route('rap-reports.index'))
            ->assertOk()
            ->assertSee('value="'.(RapReportEntry::query()->max('report_date') ?? now()->toDateString()).'"', false);

        $this->actingAs($user)
            ->get(route('cash-balances.index'))
            ->assertOk()
            ->assertSee('value="'.CashBalance::query()->max('balance_date').'"', false);

        $this->actingAs($user)
            ->get(route('trading-stock-balances.index'))
            ->assertOk()
            ->assertSee('value="'.TradingStockBalance::query()->max('balance_date').'"', false);

        $this->actingAs($user)
            ->get(route('debt-credit-reports.index'))
            ->assertOk()
            ->assertSee('value="'.DebtCreditEntry::query()->max('report_date').'"', false);
    }
}
