<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use App\Models\Skill;
use Illuminate\Support\Facades\Schema;

it('creates the job search tables', function () {
    expect(Schema::hasTable('companies'))->toBeTrue()
        ->and(Schema::hasTable('categories'))->toBeTrue()
        ->and(Schema::hasTable('skills'))->toBeTrue()
        ->and(Schema::hasTable('locations'))->toBeTrue()
        ->and(Schema::hasTable('job_listings'))->toBeTrue()
        ->and(Schema::hasTable('category_job_listing'))->toBeTrue()
        ->and(Schema::hasTable('job_listing_skill'))->toBeTrue();
});

it('creates a job listing with its relationships', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $skills = Skill::factory()->count(3)->create();

    $jobListing = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create();

    $jobListing->categories()->sync($categories->pluck('id')->all());
    $jobListing->skills()->sync(
        $skills->values()->mapWithKeys(fn (Skill $skill, int $index): array => [
            $skill->id => [
                'is_primary' => $index === 0,
                'weight' => max(1, 3 - $index),
            ],
        ])->all(),
    );

    $jobListing->refresh();

    expect($jobListing->company)->id->toBe($company->id)
        ->and($jobListing->primaryLocation)->id->toBe($location->id)
        ->and($jobListing->categories)->toHaveCount(2)
        ->and($jobListing->skills)->toHaveCount(3)
        ->and($jobListing->toSearchDocument()['company_name'])->toBe($company->name);
});
