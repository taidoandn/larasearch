<?php

use App\Enums\CompanyStatus;
use App\Enums\ExperienceLevel;
use App\Enums\JobListingSourceType;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use App\Models\Skill;
use Illuminate\Support\Facades\Queue;
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
    Queue::fake();

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

it('uses string storage for enum-backed columns and search facets', function () {
    expect(Schema::getColumnType('companies', 'status'))->toBe('varchar')
        ->and(Schema::getColumnType('job_listings', 'source_type'))->toBe('varchar')
        ->and(Schema::getColumnType('job_listings', 'job_type'))->toBe('varchar')
        ->and(Schema::getColumnType('job_listings', 'work_model'))->toBe('varchar')
        ->and(Schema::getColumnType('job_listings', 'experience_level'))->toBe('varchar');
});

it('casts company and job listing enum-backed attributes', function () {
    Queue::fake();

    $company = Company::factory()->create();

    $jobListing = JobListing::factory()->create([
        'company_id' => $company->id,
    ])->fresh();

    expect($company->fresh()->status)->toBeInstanceOf(CompanyStatus::class)
        ->and($jobListing->job_type)->toBeInstanceOf(JobType::class)
        ->and($jobListing->work_model)->toBeInstanceOf(WorkModel::class)
        ->and($jobListing->experience_level)->toBeInstanceOf(ExperienceLevel::class)
        ->and($jobListing->source_type)->toBeInstanceOf(JobListingSourceType::class)
        ->and($jobListing->toSearchDocument()['job_type'])->toBe($jobListing->job_type->value)
        ->and($jobListing->toSearchDocument()['work_model'])->toBe($jobListing->work_model->value)
        ->and($jobListing->toSearchDocument()['experience_level'])->toBe($jobListing->experience_level->value);
});
