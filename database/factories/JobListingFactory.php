<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        $title = fake()->randomElement([
            'Senior Backend Engineer',
            'Lead Laravel Developer',
            'Full Stack Engineer',
            'Platform Reliability Engineer',
            'Search Infrastructure Engineer',
            'Data Platform Engineer',
        ]);

        $slug = Str::slug($title).'-'.fake()->unique()->numberBetween(10000, 99999);
        $publishedAt = fake()->dateTimeBetween('-30 days', 'now');
        $salaryMin = fake()->numberBetween(1200, 4000);
        $salaryMax = $salaryMin + fake()->numberBetween(200, 3000);

        return [
            'company_id' => Company::factory(),
            'primary_location_id' => Location::factory(),
            'slug' => $slug,
            'title' => $title,
            'normalized_title' => Str::lower($title),
            'short_description' => fake()->sentence(18),
            'description' => fake()->paragraphs(4, true),
            'requirements' => fake()->paragraphs(2, true),
            'benefits' => fake()->paragraphs(2, true),
            'job_type' => fake()->randomElement(['full-time', 'contract', 'internship']),
            'work_model' => fake()->randomElement(['onsite', 'hybrid', 'remote']),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead']),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'salary_currency' => fake()->randomElement(['USD', 'VND', 'SGD']),
            'salary_is_visible' => true,
            'application_url' => fake()->url(),
            'is_featured' => fake()->boolean(15),
            'is_active' => true,
            'published_at' => $publishedAt,
            'expires_at' => (clone $publishedAt)->modify('+30 days'),
            'source_type' => fake()->randomElement(['direct', 'imported']),
        ];
    }
}
