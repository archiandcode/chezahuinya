<?php

namespace Tests\Feature;

use App\Models\CashCompany;
use App\Models\CashFlowCategory;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class CashTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_cash_transactions(): void
    {
        $this->get(route('cash-transactions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_selected_cash_register_is_applied_as_context_and_hidden_from_filter_block(): void
    {
        $user = User::factory()->create();
        $selectedRegister = CashRegister::factory()->create(['name' => 'Региональная касса '.str()->random(8)]);
        $otherRegister = CashRegister::factory()->create(['name' => 'Другая касса '.str()->random(8)]);

        CashTransaction::factory()->forCashRegister($selectedRegister)->income(1000)->create([
            'transaction_date' => '2026-05-18',
        ]);
        CashTransaction::factory()->forCashRegister($otherRegister)->income(2000)->create([
            'transaction_date' => '2026-05-18',
        ]);

        $this->actingAs($user)
            ->get(route('cash-transactions.index', ['cash_register_id' => $selectedRegister->id]))
            ->assertOk()
            ->assertSee('<h1 class="m-0">'.$selectedRegister->name.'</h1>', false)
            ->assertSee('1 000.00')
            ->assertDontSee('2 000.00')
            ->assertDontSee('for="cash_register_id"', false)
            ->assertSee('name="cash_register_id" value="'.$selectedRegister->id.'"', false);
    }

    public function test_cash_transaction_filters_apply_inside_selected_register(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create(['name' => 'Основная касса '.str()->random(8)]);
        $company = CashCompany::factory()->create(['name' => 'ТОО Альфа '.str()->random(8)]);
        $otherCompany = CashCompany::factory()->create(['name' => 'ТОО Бета '.str()->random(8)]);
        $cashFlow = CashFlowCategory::factory()->income()->create(['name' => 'Продажи '.str()->random(8)]);
        $otherCashFlow = CashFlowCategory::factory()->expense()->create(['name' => 'Аренда '.str()->random(8)]);

        CashTransaction::factory()
            ->forCashRegister($register)
            ->forCashCompany($company)
            ->forCashFlowCategory($cashFlow)
            ->income(1500)
            ->create([
                'transaction_date' => '2026-05-18',
                'has_supporting_document' => true,
            ]);
        CashTransaction::factory()
            ->forCashRegister($register)
            ->forCashCompany($otherCompany)
            ->forCashFlowCategory($otherCashFlow)
            ->expense(700)
            ->create([
                'transaction_date' => '2026-05-19',
                'has_supporting_document' => false,
            ]);

        $this->actingAs($user)
            ->get(route('cash-transactions.index', [
                'cash_register_id' => $register->id,
                'date_from' => '2026-05-18',
                'date_to' => '2026-05-18',
                'cash_company_id' => $company->id,
                'cash_flow_category_id' => $cashFlow->id,
                'direction' => 'income',
                'has_supporting_document' => 'yes',
            ]))
            ->assertOk()
            ->assertSee('1 500.00')
            ->assertDontSee('700.00')
            ->assertSee('Дата от:')
            ->assertSee('Компания:')
            ->assertSee('ДДС:')
            ->assertSee('Тип операции:')
            ->assertSee('СЗ:');
    }

    public function test_cash_transaction_can_be_created_and_redirects_back_to_current_filters(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create(['name' => 'Основная касса '.str()->random(8)]);
        $company = CashCompany::factory()->create(['name' => 'ТОО Альфа '.str()->random(8)]);
        $cashFlow = CashFlowCategory::factory()->income()->create(['name' => 'Продажи '.str()->random(8)]);

        $this->actingAs($user)
            ->post(route('cash-transactions.store'), [
                'cash_register_id' => $register->id,
                'transaction_date' => '2026-05-18',
                'income_amount' => '3000.25',
                'expense_amount' => null,
                'cash_company_id' => $company->id,
                'cash_flow_category_id' => $cashFlow->id,
                'has_supporting_document' => '1',
                'filter_cash_register_id' => $register->id,
                'filter_date_from' => '2026-05-01',
                'filter_direction' => 'income',
                'filter_per_page' => '25',
            ])
            ->assertRedirect(route('cash-transactions.index', [
                'cash_register_id' => $register->id,
                'date_from' => '2026-05-01',
                'direction' => 'income',
                'per_page' => '25',
            ]));

        $this->assertDatabaseHas('cash_transactions', [
            'cash_register_id' => $register->id,
            'transaction_date' => '2026-05-18',
            'income_amount' => '3000.25',
            'expense_amount' => '0.00',
            'cash_company_id' => $company->id,
            'company' => $company->name,
            'cash_flow_category_id' => $cashFlow->id,
            'cash_flow' => $cashFlow->name,
            'has_supporting_document' => true,
        ]);
    }

    public function test_cash_transaction_create_validation_requires_amount_and_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cash-transactions.store'), [
                'transaction_date' => '2026-05-18',
                'income_amount' => '0',
                'expense_amount' => '0',
            ])
            ->assertSessionHasErrors(['cash_register_id']);

        $register = CashRegister::factory()->create(['name' => 'Валидационная касса '.str()->random(8)]);

        $this->actingAs($user)
            ->post(route('cash-transactions.store'), [
                'cash_register_id' => $register->id,
                'transaction_date' => '2026-05-18',
                'income_amount' => '0',
                'expense_amount' => '0',
            ])
            ->assertSessionHasErrors(['income_amount']);
    }

    public function test_cash_transaction_can_be_updated_and_keeps_filter_context(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create(['name' => 'Основная касса '.str()->random(8)]);
        $company = CashCompany::factory()->create(['name' => 'ТОО Альфа '.str()->random(8)]);
        $cashFlow = CashFlowCategory::factory()->expense()->create(['name' => 'Аренда '.str()->random(8)]);
        $transaction = CashTransaction::factory()->forCashRegister($register)->income(100)->create([
            'transaction_date' => '2026-05-18',
        ]);

        $this->actingAs($user)
            ->put(route('cash-transactions.update', $transaction), [
                'cash_register_id' => $register->id,
                'transaction_date' => '2026-05-19',
                'income_amount' => null,
                'expense_amount' => '650.75',
                'cash_company_id' => $company->id,
                'cash_flow_category_id' => $cashFlow->id,
                'has_supporting_document' => '0',
                'filter_cash_register_id' => $register->id,
                'filter_cash_company_id' => $company->id,
                'filter_per_page' => '50',
                'filter_page' => '2',
            ])
            ->assertRedirect(route('cash-transactions.index', [
                'cash_register_id' => $register->id,
                'cash_company_id' => $company->id,
                'per_page' => '50',
                'page' => '2',
            ]));

        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'transaction_date' => '2026-05-19',
            'income_amount' => '0.00',
            'expense_amount' => '650.75',
            'cash_company_id' => $company->id,
            'company' => $company->name,
            'cash_flow_category_id' => $cashFlow->id,
            'cash_flow' => $cashFlow->name,
            'has_supporting_document' => false,
        ]);
    }

    public function test_cash_transaction_update_validation_rejects_negative_amounts(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create(['name' => 'Основная касса '.str()->random(8)]);
        $transaction = CashTransaction::factory()->forCashRegister($register)->income(100)->create([
            'transaction_date' => '2026-05-18',
        ]);

        $this->actingAs($user)
            ->put(route('cash-transactions.update', $transaction), [
                'cash_register_id' => $register->id,
                'transaction_date' => '2026-05-18',
                'income_amount' => '-1',
                'expense_amount' => '0',
            ])
            ->assertSessionHasErrors(['income_amount']);
    }

    public function test_cash_transaction_can_be_deleted_and_keeps_query_context(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create(['name' => 'Основная касса '.str()->random(8)]);
        $transaction = CashTransaction::factory()->forCashRegister($register)->expense(500)->create([
            'transaction_date' => '2026-05-18',
        ]);

        $this->actingAs($user)
            ->delete(route('cash-transactions.destroy', $transaction), [
                'cash_register_id' => $register->id,
                'date_from' => '2026-05-01',
                'direction' => 'expense',
                'per_page' => '25',
                'page' => '3',
            ])
            ->assertRedirect(route('cash-transactions.index', [
                'cash_register_id' => $register->id,
                'date_from' => '2026-05-01',
                'direction' => 'expense',
                'per_page' => '25',
                'page' => '3',
            ]));

        $this->assertDatabaseMissing('cash_transactions', [
            'id' => $transaction->id,
        ]);
    }
}
