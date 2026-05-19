<?php

namespace Tests\Feature;

use App\Models\CashBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class CashBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_cash_balances(): void
    {
        $this->get(route('cash-balances.index'))
            ->assertRedirect(route('login'));
    }

    public function test_cash_balance_filters_apply_to_rows_and_summary_totals(): void
    {
        $user = User::factory()->create();
        CashBalance::query()->delete();

        CashBalance::create([
            'balance_date' => '2026-05-18',
            'sort_order' => 2,
            'company' => 'ТОО Альфа',
            'balance_amount' => 1500,
            'custody_assets_amount' => 200,
            'own_assets_amount' => 300,
        ]);
        CashBalance::create([
            'balance_date' => '2026-05-18',
            'sort_order' => 1,
            'company' => 'ТОО Бета',
            'balance_amount' => 700,
            'custody_assets_amount' => 50,
            'own_assets_amount' => 60,
        ]);
        CashBalance::create([
            'balance_date' => '2026-05-19',
            'sort_order' => 3,
            'company' => 'ТОО Альфа',
            'balance_amount' => 2500,
            'custody_assets_amount' => 400,
            'own_assets_amount' => 500,
        ]);

        $this->actingAs($user)
            ->get(route('cash-balances.index', [
                'date_from' => '2026-05-18',
                'date_to' => '2026-05-18',
                'company' => 'ТОО Альфа',
                'amount_from' => '1000',
                'amount_to' => '2000',
            ]))
            ->assertOk()
            ->assertSee('1 500.00')
            ->assertSee('200.00')
            ->assertSee('300.00')
            ->assertSee('Остатки ДС: 1')
            ->assertSee('<strong>Компания:</strong> ТОО Альфа', false)
            ->assertDontSee('700.00')
            ->assertDontSee('2 500.00');
    }

    public function test_cash_balance_rows_are_ordered_by_date_sort_order_and_id(): void
    {
        $user = User::factory()->create();
        CashBalance::query()->delete();

        CashBalance::create([
            'balance_date' => '2026-05-18',
            'sort_order' => 2,
            'company' => 'Третья компания',
            'balance_amount' => 300,
        ]);
        CashBalance::create([
            'balance_date' => '2026-05-19',
            'sort_order' => 2,
            'company' => 'Вторая компания',
            'balance_amount' => 200,
        ]);
        CashBalance::create([
            'balance_date' => '2026-05-19',
            'sort_order' => 1,
            'company' => 'Первая компания',
            'balance_amount' => 100,
        ]);

        $this->actingAs($user)
            ->get(route('cash-balances.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Первая компания',
                'Вторая компания',
                'Третья компания',
            ]);
    }

    public function test_cash_balance_index_validation_rejects_invalid_filter_combinations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cash-balances.index', [
                'date_from' => '2026-05-20',
                'date_to' => '2026-05-18',
                'amount_from' => '-1',
                'amount_to' => '-10',
                'per_page' => '500',
            ]))
            ->assertSessionHasErrors([
                'date_to',
                'amount_from',
                'amount_to',
                'per_page',
            ]);
    }

    public function test_cash_balance_can_be_created_with_default_zero_amounts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cash-balances.store'), [
                'balance_date' => '2026-05-18',
                'company' => 'ТОО Новая компания',
                'balance_amount' => null,
                'custody_assets_amount' => null,
                'own_assets_amount' => null,
            ])
            ->assertRedirect(route('cash-balances.index'));

        $this->assertDatabaseHas('cash_balances', [
            'balance_date' => '2026-05-18',
            'sort_order' => 0,
            'company' => 'ТОО Новая компания',
            'balance_amount' => '0.00',
            'custody_assets_amount' => '0.00',
            'own_assets_amount' => '0.00',
        ]);
    }

    public function test_cash_balance_update_keeps_filter_context_and_rejects_negative_amounts(): void
    {
        $user = User::factory()->create();
        $balance = CashBalance::create([
            'balance_date' => '2026-05-18',
            'sort_order' => 4,
            'company' => 'ТОО Альфа',
            'balance_amount' => 100,
            'custody_assets_amount' => 20,
            'own_assets_amount' => 30,
        ]);

        $this->actingAs($user)
            ->put(route('cash-balances.update', $balance), [
                'balance_date' => '2026-05-19',
                'sort_order' => '8',
                'company' => 'ТОО Альфа обновлено',
                'balance_amount' => '900.50',
                'custody_assets_amount' => '100.25',
                'own_assets_amount' => '200.75',
                'filter_date_from' => '2026-05-01',
                'filter_company' => 'ТОО Альфа',
                'filter_amount_from' => '50',
                'filter_per_page' => '25',
                'filter_page' => '2',
            ])
            ->assertRedirect(route('cash-balances.index', [
                'date_from' => '2026-05-01',
                'company' => 'ТОО Альфа',
                'amount_from' => '50',
                'per_page' => '25',
                'page' => '2',
            ]));

        $this->assertDatabaseHas('cash_balances', [
            'id' => $balance->id,
            'balance_date' => '2026-05-19',
            'sort_order' => 8,
            'company' => 'ТОО Альфа обновлено',
            'balance_amount' => '900.50',
            'custody_assets_amount' => '100.25',
            'own_assets_amount' => '200.75',
        ]);

        $this->actingAs($user)
            ->put(route('cash-balances.update', $balance), [
                'balance_date' => '2026-05-19',
                'company' => 'ТОО Альфа обновлено',
                'balance_amount' => '-1',
            ])
            ->assertSessionHasErrors(['balance_amount']);
    }

    public function test_cash_balance_destroy_keeps_filter_context(): void
    {
        $user = User::factory()->create();
        $balance = CashBalance::create([
            'balance_date' => '2026-05-18',
            'sort_order' => 1,
            'company' => 'ТОО На удаление',
            'balance_amount' => 100,
            'custody_assets_amount' => 0,
            'own_assets_amount' => 0,
        ]);

        $this->actingAs($user)
            ->delete(route('cash-balances.destroy', $balance), [
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-31',
                'company' => 'ТОО На удаление',
                'amount_from' => '50',
                'amount_to' => '150',
                'per_page' => '10',
                'page' => '3',
            ])
            ->assertRedirect(route('cash-balances.index', [
                'date_from' => '2026-05-01',
                'date_to' => '2026-05-31',
                'company' => 'ТОО На удаление',
                'amount_from' => '50',
                'amount_to' => '150',
                'per_page' => '10',
                'page' => '3',
            ]));

        $this->assertDatabaseMissing('cash_balances', [
            'id' => $balance->id,
        ]);
    }
}
