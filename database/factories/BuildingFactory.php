<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Organization;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
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
            'property_id' => Property::factory(),
            'name' => 'Haus '.fake()->randomLetter(),
            'floors' => fake()->numberBetween(1, 8),
        ];
    }
}
