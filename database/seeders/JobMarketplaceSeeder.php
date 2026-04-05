<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobMarketplaceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $companies = Company::factory()->count(250)->create();
        $locations = Location::factory()->count(80)->create();
        $categories = Category::factory()->count(12)->create();
        $skills = Skill::factory()->count(12)->create();

        $jobCount = (int) env('JOB_MARKETPLACE_SEED_COUNT', 5_000);

        JobListing::factory()
            ->count($jobCount)
            ->state(fn (): array => [
                'company_id' => $companies->random()->id,
                'primary_location_id' => $locations->random()->id,
            ])
            ->create()
            ->each(function (JobListing $jobListing) use ($categories, $skills): void {
                $jobListing->categories()->sync(
                    $categories->random(rand(1, 3))->pluck('id')->all(),
                );

                $selectedSkills = $skills->random(rand(2, 6));

                $jobListing->skills()->sync(
                    $selectedSkills
                        ->values()
                        ->mapWithKeys(fn (Skill $skill, int $index): array => [
                            $skill->id => [
                                'is_primary' => $index === 0,
                                'weight' => max(1, 5 - $index),
                            ],
                        ])->all(),
                );
            });
    }
}
