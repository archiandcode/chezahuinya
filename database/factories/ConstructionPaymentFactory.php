<?php

namespace Database\Factories;

use App\Models\ConstructionPayment;
use App\Models\ConstructionSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConstructionPayment>
 */
class ConstructionPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'construction_section_id' => ConstructionSection::factory(),
            'payment_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'supplier' => 'Поставщик '.$this->faker->unique()->bothify('###'),
            'amount' => $this->faker->randomFloat(2, 1, 500000),
            'contract' => 'Договор '.$this->faker->unique()->bothify('###'),
            'purpose' => 'Назначение платежа',
            'payment_source' => 'Касса '.$this->faker->unique()->bothify('###'),
        ];
    }

    public function forSection(ConstructionSection $section): static
    {
        return $this->state(fn (): array => [
            'construction_section_id' => $section->id,
        ]);
    }
}
