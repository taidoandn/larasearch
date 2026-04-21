<?php

use App\Contracts\SearchServiceInterface;
use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use App\Models\Skill;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page for jobs', function () {
    $response = $this->get(route('jobs.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the jobs index page', function () {
    $user = User::factory()->create();
    $searchService = Mockery::mock(SearchServiceInterface::class);
    $searchService->shouldReceive('search')
        ->once()
        ->andReturn([
            'items' => [],
            'pagination' => [
                'page' => 1,
                'per_page' => 20,
                'total' => 0,
                'from' => 0,
                'to' => 0,
                'total_pages' => 0,
                'has_more' => false,
            ],
            'facets' => [
                'locations' => [],
                'categories' => [],
                'skills' => [],
                'job_types' => [],
                'work_models' => [],
                'experience_levels' => [],
            ],
            'sort' => 'best_match',
        ]);

    app()->instance(SearchServiceInterface::class, $searchService);

    $response = $this->actingAs($user)->get(route('jobs.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('jobs/index'),
    );
});

test('authenticated users can visit the job show page', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create([
        'name' => 'Acme Tech',
        'slug' => 'acme-tech',
        'logo_url' => 'https://cdn.example.test/acme-logo.png',
        'industry' => 'Cloud Infrastructure',
        'company_size' => '201-500',
        'founded_year' => 2016,
        'is_verified' => true,
    ]);
    $location = Location::factory()->create([
        'city_name' => 'Da Nang',
        'display_name' => 'Da Nang',
    ]);
    $skill = Skill::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $jobListing = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => 'lead-technical-architect',
            'title' => 'Lead Technical Architect',
            'job_type' => JobType::FULL_TIME,
            'work_model' => WorkModel::ONSITE,
            'experience_level' => ExperienceLevel::ENTRY,
            'benefits' => "Own reliability initiatives\nShape delivery workflows",
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

    $jobListing->skills()->sync([
        $skill->id => ['is_primary' => true, 'weight' => 5],
    ]);

    $response = $this->actingAs($user)->get(route('jobs.show', $jobListing->slug));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('jobs/show')
        ->where('job', fn ($job): bool => data_get($job, 'slug') === 'lead-technical-architect'
            && data_get($job, 'title') === 'Lead Technical Architect'
            && data_get($job, 'application_url') === $jobListing->application_url
            && data_get($job, 'company.name') === 'Acme Tech'
            && data_get($job, 'company.logo_url') === 'https://cdn.example.test/acme-logo.png'
            && data_get($job, 'company.website') === $company->website_url
            && data_get($job, 'company.summary') === $company->description
            && data_get($job, 'company.industry') === 'Cloud Infrastructure'
            && data_get($job, 'company.company_size') === '201-500'
            && data_get($job, 'company.founded_year') === 2016
            && data_get($job, 'company.is_verified') === true
            && data_get($job, 'primary_location') === 'Da Nang'
            && data_get($job, 'overview') === $jobListing->description
            && data_get($job, 'job_type') === 'full-time'
            && data_get($job, 'job_type_label') === JobType::FULL_TIME->label()
            && data_get($job, 'work_model_label') === WorkModel::ONSITE->label()
            && data_get($job, 'experience_level_label') === ExperienceLevel::ENTRY->label()
            && data_get($job, 'skills.0') === 'Laravel'
            && data_get($job, 'benefits.0') === 'Own reliability initiatives'
            && data_get($job, 'benefits.1') === 'Shape delivery workflows'
            && data_get($job, 'summary_metrics.0.label') === 'Compensation')
        ->where('searchContext.index_query', [])
        ->has('job.requirements')
        ->has('job.benefits', 2)
        ->has('job.summary_metrics')
        ->has('relatedJobs'),
    );
});

test('job show preserves only meaningful search context query params', function () {
    $user = User::factory()->create();
    $jobListing = JobListing::factory()->create([
        'slug' => 'mobile-engineer-role',
        'published_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($user)->get(route('jobs.show', [
        'job' => $jobListing->slug,
        'category' => 'mobile-engineering',
        'q' => '',
        'location' => [],
        'job_type' => '',
        'work_model' => [],
        'experience_level' => [],
        'salary_min' => '',
        'salary_max' => '',
        'sort' => 'best_match',
        'page' => 1,
        'per_page' => 20,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('jobs/show')
        ->where('searchContext.index_query', [
            'category' => ['mobile-engineering'],
        ]),
    );
});

test('job show drops invalid search context query params before linking back to the index', function () {
    $user = User::factory()->create();
    $jobListing = JobListing::factory()->create([
        'slug' => 'search-context-sanitizer-role',
        'published_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($user)->get(route('jobs.show', [
        'job' => $jobListing->slug,
        'category' => 'Platform Engineering',
        'q' => 'laravel',
        'job_type' => 'invalid-job-type',
        'work_model' => ['remote', 'bad-work-model'],
        'experience_level' => ['senior', 'unknown-level'],
        'sort' => 'unsupported',
        'page' => 0,
        'per_page' => 999,
        'salary_min' => -200,
        'salary_max' => 'abc',
        'skills' => ['Laravel', '', str_repeat('x', 121)],
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('jobs/show')
        ->where('searchContext.index_query', [
            'category' => ['platform-engineering'],
            'q' => 'laravel',
            'skills' => ['laravel'],
            'work_model' => ['remote'],
            'experience_level' => ['senior'],
            'per_page' => 50,
        ]),
    );
});

test('job show returns a 404 when the listing is not visible in search', function (array $overrides) {
    $user = User::factory()->create();
    $company = Company::factory()->create([
        'name' => 'Acme Tech',
        'slug' => 'acme-tech',
    ]);

    $jobListing = JobListing::factory()
        ->for($company)
        ->create(array_merge([
            'slug' => 'lead-technical-architect',
            'title' => 'Lead Technical Architect',
            'job_type' => JobType::FULL_TIME,
            'is_active' => true,
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ], $overrides));

    $this->actingAs($user)
        ->get(route('jobs.show', $jobListing->slug))
        ->assertNotFound();
})->with([
    'inactive listing' => [['is_active' => false]],
    'future publication' => [
        ['published_at' => now()->addDay()],
    ],
    'expired listing' => [
        ['expires_at' => now()->subMinute()],
    ],
]);

test('job show related jobs exclude listings that are hidden from search', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create([
        'name' => 'Acme Tech',
        'slug' => 'acme-tech',
    ]);
    $location = Location::factory()->create([
        'city_name' => 'Da Nang',
        'display_name' => 'Da Nang',
    ]);
    $skill = Skill::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $jobListing = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => 'visible-parent-job',
            'title' => 'Visible Parent Job',
            'published_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

    $visibleRelatedJob = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => 'visible-related-job',
            'title' => 'Visible Related Job',
            'published_at' => now()->subHour(),
            'expires_at' => now()->addDay(),
        ]);

    $futureRelatedJob = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => 'future-related-job',
            'title' => 'Future Related Job',
            'published_at' => now()->addHour(),
            'expires_at' => now()->addDay(),
        ]);

    $expiredRelatedJob = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => 'expired-related-job',
            'title' => 'Expired Related Job',
            'published_at' => now()->subDay(),
            'expires_at' => now()->subMinute(),
        ]);

    foreach ([$jobListing, $visibleRelatedJob, $futureRelatedJob, $expiredRelatedJob] as $job) {
        $job->skills()->sync([
            $skill->id => ['is_primary' => true, 'weight' => 5],
        ]);
    }

    $response = $this->actingAs($user)->get(route('jobs.show', $jobListing->slug));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->has('relatedJobs', 1)
        ->where('relatedJobs.0.slug', $visibleRelatedJob->slug),
    );
});

test('job show returns a 404 for an unknown slug', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('jobs.show', 'missing-job'))
        ->assertNotFound();
});
