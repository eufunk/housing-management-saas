<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
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
            'name' => fake()->streetName().' Apartments',
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => 'DE',
        ];
    }
}
