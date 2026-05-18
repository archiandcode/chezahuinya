<?php

namespace Database\Factories;

use App\Models\CashCompany;
use App\Models\CashFlowCategory;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashTransaction>
 */
class CashTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'income_amount' => $this->faker->randomFloat(2, 1, 500000),
            'expense_amount' => 0,
            'cash_company_id' => null,
            'company' => null,
            'cash_flow_category_id' => null,
            'cash_flow' => null,
            'has_supporting_document' => $this->faker->optional()->boolean(),
        ];
    }

    public function forCashRegister(CashRegister $cashRegister): static
    {
        return $this->state(fn (): array => [
            'cash_register_id' => $cashRegister->id,
        ]);
    }

    public function forCashCompany(CashCompany $cashCompany): static
    {
        return $this->state(fn (): array => [
            'cash_company_id' => $cashCompany->id,
            'company' => $cashCompany->name,
        ]);
    }

    public function forCashFlowCategory(CashFlowCategory $cashFlowCategory): static
    {
        return $this->state(fn (): array => [
            'cash_flow_category_id' => $cashFlowCategory->id,
            'cash_flow' => $cashFlowCategory->name,
        ]);
    }

    public function income(float $amount = 1000): static
    {
        return $this->state(fn (): array => [
            'income_amount' => $amount,
            'expense_amount' => 0,
        ]);
    }

    public function expense(float $amount = 1000): static
    {
        return $this->state(fn (): array => [
            'income_amount' => 0,
            'expense_amount' => $amount,
        ]);
    }
}
