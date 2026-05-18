<?php

namespace Database\Factories;

use App\Models\ConstructionSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConstructionSection>
 */
class ConstructionSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Раздел стройки '.$this->faker->unique()->bothify('###'),
            'sort_order' => $this->faker->numberBetween(10, 100),
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
