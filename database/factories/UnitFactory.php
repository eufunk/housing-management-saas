<?php

namespace Database\Factories;

use App\Enums\UnitStatus;
use App\Models\Building;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'building_id' => Building::factory(),
            'unit_number' => (string) fake()->unique()->numberBetween(1, 999),
            'floor' => fake()->numberBetween(0, 8),
            'size_sqm' => fake()->randomFloat(2, 25, 140),
            'rooms' => fake()->numberBetween(1, 5),
            'status' => UnitStatus::Vacant->value,
        ];
    }
}
