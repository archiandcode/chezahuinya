<?php

namespace Database\Factories;

use App\Models\CashCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashCompany>
 */
class CashCompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = 'ТОО '.$this->faker->unique()->company();

        return [
            'name' => $name,
            'short_name' => $this->faker->optional()->lexify('???'),
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
