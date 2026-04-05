<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $city = fake()->city();
        $countryCode = fake()->randomElement(['US', 'VN', 'SG', 'TH']);
        $district = fake()->optional()->citySuffix();

        return [
            'country_code' => $countryCode,
            'state_name' => fake()->optional()->state(),
            'city_name' => $city,
            'district_name' => $district,
            'display_name' => collect([$district, $city, $countryCode])->filter()->implode(', '),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'is_active' => true,
        ];
    }
}
