<?php

namespace Database\Factories;

use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashRegister>
 */
class CashRegisterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Касса '.$this->faker->unique()->bothify('###'),
            'currency' => 'KZT',
            'opening_balance' => $this->faker->randomFloat(2, 0, 500000),
            'opening_balance_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
