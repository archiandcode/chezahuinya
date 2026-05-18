<?php

namespace Database\Factories;

use App\Models\CashFlowCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashFlowCategory>
 */
class CashFlowCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Статья '.$this->faker->unique()->bothify('###'),
            'direction' => $this->faker->optional()->randomElement(['income', 'expense']),
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (): array => [
            'direction' => 'income',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => [
            'direction' => 'expense',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
