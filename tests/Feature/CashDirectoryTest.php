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
class CashDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_cash_directories(): void
    {
        $this->get(route('cash-directories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_cash_directories_page_shows_existing_entries(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create([
            'name' => 'Офисная касса',
            'currency' => 'KZT',
            'opening_balance' => 1000,
            'opening_balance_date' => '2026-05-18',
        ]);
        $company = CashCompany::factory()->create([
            'name' => 'ТОО Альфа',
            'short_name' => 'Альфа',
        ]);
        $cashFlow = CashFlowCategory::factory()->income()->create([
            'name' => 'Продажи',
        ]);

        $this->actingAs($user)
            ->get(route('cash-directories.index'))
            ->assertOk()
            ->assertSee($register->name)
            ->assertSee($company->name)
            ->assertSee($cashFlow->name);
    }

    public function test_cash_register_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cash-registers.store'), [
                'name' => 'Касса "A"',
                'currency' => 'kzt',
                'opening_balance' => '1500.50',
                'opening_balance_date' => '2026-05-18',
                'is_active' => '1',
            ])
            ->assertRedirect(route('cash-directories.index'));

        $cashRegister = CashRegister::query()->where('name', 'Касса "A"')->firstOrFail();

        $this->assertDatabaseHas('cash_registers', [
            'id' => $cashRegister->id,
            'name' => 'Касса "A"',
            'currency' => 'KZT',
            'opening_balance' => '1500.50',
            'opening_balance_date' => '2026-05-18',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('cash-registers.update', $cashRegister), [
                'name' => 'Касса "B"',
                'currency' => 'usd',
                'opening_balance' => '2500.75',
                'opening_balance_date' => null,
            ])
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseHas('cash_registers', [
            'id' => $cashRegister->id,
            'name' => 'Касса "B"',
            'currency' => 'USD',
            'opening_balance' => '2500.75',
            'opening_balance_date' => null,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('cash-registers.destroy', $cashRegister))
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseMissing('cash_registers', [
            'id' => $cashRegister->id,
        ]);
    }

    public function test_cash_register_validation_rejects_duplicate_names_and_invalid_currency(): void
    {
        $user = User::factory()->create();
        $name = 'Главная касса '.str()->random(8);

        CashRegister::factory()->create([
            'name' => $name,
        ]);

        $this->actingAs($user)
            ->post(route('cash-registers.store'), [
                'name' => $name,
                'currency' => 'KZ',
                'opening_balance' => '100',
            ])
            ->assertSessionHasErrors(['name', 'currency']);
    }

    public function test_cash_company_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cash-companies.store'), [
                'name' => 'Компания А',
                'short_name' => 'КА',
                'is_active' => '1',
            ])
            ->assertRedirect(route('cash-directories.index'));

        $company = CashCompany::query()->where('name', 'Компания А')->firstOrFail();

        $this->actingAs($user)
            ->put(route('cash-companies.update', $company), [
                'name' => 'Компания Б',
                'short_name' => '',
            ])
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseHas('cash_companies', [
            'id' => $company->id,
            'name' => 'Компания Б',
            'short_name' => null,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('cash-companies.destroy', $company))
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseMissing('cash_companies', [
            'id' => $company->id,
        ]);
    }

    public function test_cash_company_validation_rejects_duplicate_names(): void
    {
        $user = User::factory()->create();
        $name = 'ТОО Альфа '.str()->random(8);

        CashCompany::factory()->create([
            'name' => $name,
        ]);

        $this->actingAs($user)
            ->post(route('cash-companies.store'), [
                'name' => $name,
            ])
            ->assertSessionHasErrors(['name']);
    }

    public function test_cash_flow_category_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cash-flow-categories.store'), [
                'name' => 'Продажи',
                'direction' => 'income',
                'is_active' => '1',
            ])
            ->assertRedirect(route('cash-directories.index'));

        $cashFlow = CashFlowCategory::query()->where('name', 'Продажи')->firstOrFail();

        $this->actingAs($user)
            ->put(route('cash-flow-categories.update', $cashFlow), [
                'name' => 'Аренда',
                'direction' => 'expense',
            ])
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseHas('cash_flow_categories', [
            'id' => $cashFlow->id,
            'name' => 'Аренда',
            'direction' => 'expense',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('cash-flow-categories.destroy', $cashFlow))
            ->assertRedirect(route('cash-directories.index'));

        $this->assertDatabaseMissing('cash_flow_categories', [
            'id' => $cashFlow->id,
        ]);
    }

    public function test_cash_flow_category_validation_rejects_duplicate_names_and_invalid_direction(): void
    {
        $user = User::factory()->create();
        $name = 'Продажи '.str()->random(8);

        CashFlowCategory::factory()->income()->create([
            'name' => $name,
        ]);

        $this->actingAs($user)
            ->post(route('cash-flow-categories.store'), [
                'name' => $name,
                'direction' => 'other',
            ])
            ->assertSessionHasErrors(['name', 'direction']);
    }

    public function test_deleting_used_directory_entries_keeps_cash_transactions_with_empty_foreign_keys(): void
    {
        $user = User::factory()->create();
        $register = CashRegister::factory()->create([
            'name' => 'Удаляемая касса',
        ]);
        $company = CashCompany::factory()->create([
            'name' => 'Удаляемая компания',
        ]);
        $cashFlow = CashFlowCategory::factory()->expense()->create([
            'name' => 'Удаляемая статья',
        ]);
        $transaction = CashTransaction::factory()
            ->forCashRegister($register)
            ->forCashCompany($company)
            ->forCashFlowCategory($cashFlow)
            ->expense(100)
            ->create([
                'transaction_date' => '2026-05-18',
            ]);

        $this->actingAs($user)->delete(route('cash-registers.destroy', $register));
        $this->actingAs($user)->delete(route('cash-companies.destroy', $company));
        $this->actingAs($user)->delete(route('cash-flow-categories.destroy', $cashFlow));

        $this->assertDatabaseHas('cash_transactions', [
            'id' => $transaction->id,
            'cash_register_id' => null,
            'cash_company_id' => null,
            'cash_flow_category_id' => null,
        ]);
    }

    public function test_cash_menu_only_shows_directories_when_there_are_no_cash_registers(): void
    {
        $user = User::factory()->create();
        CashRegister::query()->delete();

        $this->actingAs($user)
            ->get(route('cash-directories.index'))
            ->assertOk()
            ->assertDontSee('<p>Отчет ОДДС</p>', false)
            ->assertSee('<p>Справочники</p>', false);
    }
}
