<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'name' => $name,
            'legal_name' => "{$name} LLC",
            'description' => fake()->paragraphs(2, true),
            'website_url' => fake()->url(),
            'logo_url' => fake()->imageUrl(256, 256, 'business'),
            'industry' => fake()->randomElement(['Software', 'Fintech', 'AI', 'E-commerce', 'Cloud']),
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'founded_year' => fake()->numberBetween(1998, 2024),
            'country_code' => fake()->randomElement(['US', 'VN', 'SG', 'TH']),
            'is_verified' => fake()->boolean(35),
            'status' => 'active',
        ];
    }
}
