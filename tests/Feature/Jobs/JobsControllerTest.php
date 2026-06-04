<?php

use App\Models\User;
use App\Search\Searchers\JobListingSearcher;

it('renders the jobs index page with canonical job props', function () {
    $user = User::factory()->create();

    $searchService = Mockery::mock(JobListingSearcher::class);
    $searchService->shouldReceive('search')
        ->once()
        ->with([
            'q' => 'laravel',
            'location' => ['da-nang'],
            'category' => [],
            'skills' => [],
            'job_type' => [],
            'work_model' => [],
            'experience_level' => [],
            'salary_min' => null,
            'salary_max' => null,
            'sort' => 'newest',
            'page' => 2,
            'per_page' => 10,
        ])
        ->andReturn([
            'data' => [
                [
                    'id' => 1,
                    'slug' => 'senior-laravel-backend-engineer',
                    'title' => 'Senior Laravel Backend Engineer',
                    'company' => [
                        'name' => 'Acme Tech',
                        'slug' => 'acme-tech',
                        'logo_url' => 'https://cdn.example.test/acme-logo.png',
                        'website' => 'https://acme.example.test',
                    ],
                    'application_url' => 'https://jobs.example.test/apply/senior-laravel-backend-engineer',
                    'primary_location' => 'Da Nang',
                    'locations' => ['Da Nang'],
                    'skills' => ['Laravel', 'PHP'],
                    'salary' => [
                        'min' => 1500,
                        'max' => 2500,
                        'currency' => 'USD',
                        'is_visible' => true,
                    ],
                    'job_type' => 'full-time',
                    'work_model' => 'hybrid',
                    'experience_level' => 'senior',
                    'published_at' => '2026-04-01T09:00:00Z',
                    'highlight' => [
                        'title' => null,
                        'description' => null,
                    ],
                ],
            ],
            'current_page' => 2,
            'per_page' => 10,
            'total' => 42,
            'from' => 11,
            'to' => 11,
            'last_page' => 5,
            'facets' => [
                'locations' => [],
                'categories' => [],
                'skills' => [],
                'job_types' => [],
                'work_models' => [],
                'experience_levels' => [],
            ],
            'sort' => 'newest',
        ]);

    app()->instance(JobListingSearcher::class, $searchService);

    $response = $this->actingAs($user)->get(route('jobs.index', [
        'q' => 'laravel',
        'location' => ['da-nang'],
        'sort' => 'newest',
        'page' => 2,
        'per_page' => 10,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('jobs/index')
        ->where('results.data.0.application_url', 'https://jobs.example.test/apply/senior-laravel-backend-engineer')
        ->where('results.data.0.company.logo_url', 'https://cdn.example.test/acme-logo.png')
        ->where('results.data.0.company.website', 'https://acme.example.test')
        ->where('results.current_page', 2)
        ->where('results.per_page', 10)
        ->where('results.from', 11)
        ->where('results.to', 11)
        ->where('results.sort', 'newest')
        ->where('filters.q', 'laravel')
        ->where('filters.location', ['da-nang'])
        ->where('filters.sort', 'newest')
        ->where('filters.page', 2)
        ->where('filters.per_page', 10),
    );
});

it('preserves the active category filter when category facets are empty', function () {
    $user = User::factory()->create();

    $searchService = Mockery::mock(JobListingSearcher::class);
    $searchService->shouldReceive('search')
        ->once()
        ->with([
            'q' => '',
            'location' => [],
            'category' => ['platform-engineering'],
            'skills' => [],
            'job_type' => [],
            'work_model' => [],
            'experience_level' => [],
            'salary_min' => null,
            'salary_max' => null,
            'sort' => 'best_match',
            'page' => 1,
            'per_page' => 20,
        ])
        ->andReturn([
            'data' => [],
            'current_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'from' => null,
            'to' => null,
            'last_page' => 1,
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

    app()->instance(JobListingSearcher::class, $searchService);

    $response = $this->actingAs($user)->get(route('jobs.index', [
        'category' => 'platform-engineering',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('jobs/index')
        ->where('filters.category', ['platform-engineering'])
        ->where('results.facets.categories', []),
    );
});

it('redirects guests away from job suggestions', function () {
    $this->get(route('jobs.suggest', ['q' => 'lar']))
        ->assertRedirect(route('login'));
});

it('rejects invalid enum-backed search filters', function () {
    $user = User::factory()->create();

    $response = $this->from(route('jobs.index'))
        ->actingAs($user)
        ->get(route('jobs.index', [
            'job_type' => ['temporary'],
            'work_model' => 'remote-first',
            'experience_level' => 'principal',
        ]));

    $response->assertRedirect(route('jobs.index'));
    $response->assertSessionHasErrors([
        'job_type.0',
        'work_model.0',
        'experience_level.0',
    ]);
});

it('renders normalized multi-select facet filters on the jobs index page', function () {
    $user = User::factory()->create();

    $searchService = Mockery::mock(JobListingSearcher::class);
    $searchService->shouldReceive('search')
        ->once()
        ->with([
            'q' => '',
            'location' => ['da-nang', 'bangkok'],
            'category' => ['platform-engineering', 'developer-tools'],
            'skills' => [],
            'job_type' => ['full-time', 'contract'],
            'work_model' => ['remote', 'hybrid'],
            'experience_level' => ['mid', 'senior'],
            'salary_min' => null,
            'salary_max' => null,
            'sort' => 'best_match',
            'page' => 1,
            'per_page' => 20,
        ])
        ->andReturn([
            'data' => [],
            'current_page' => 1,
            'per_page' => 20,
            'total' => 0,
            'from' => null,
            'to' => null,
            'last_page' => 1,
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

    app()->instance(JobListingSearcher::class, $searchService);

    $response = $this->actingAs($user)->get(route('jobs.index', [
        'location' => ['Da Nang', 'Bangkok'],
        'category' => ['Platform Engineering', 'Developer Tools'],
        'job_type' => ['full-time', 'contract'],
        'work_model' => ['remote', 'hybrid'],
        'experience_level' => ['mid', 'senior'],
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('jobs/index')
        ->where('filters.location', ['da-nang', 'bangkok'])
        ->where('filters.category', ['platform-engineering', 'developer-tools'])
        ->where('filters.job_type', ['full-time', 'contract'])
        ->where('filters.work_model', ['remote', 'hybrid'])
        ->where('filters.experience_level', ['mid', 'senior']),
    );
});

it('returns normalized job suggestions for authenticated users', function () {
    $user = User::factory()->create();

    $suggestService = Mockery::mock(JobListingSearcher::class);
    $suggestService->shouldReceive('suggest')
        ->once()
        ->with('lar')
        ->andReturn([
            'items' => [
                ['label' => 'Senior Laravel Backend Engineer', 'type' => 'job_title'],
                ['label' => 'Laravel', 'type' => 'skill'],
            ],
        ]);

    app()->instance(JobListingSearcher::class, $suggestService);

    $response = $this->actingAs($user)
        ->getJson(route('jobs.suggest', ['q' => 'lar']));

    $response->assertOk()
        ->assertJson([
            'items' => [
                ['label' => 'Senior Laravel Backend Engineer', 'type' => 'job_title'],
                ['label' => 'Laravel', 'type' => 'skill'],
            ],
        ]);
});

it('validates oversized job suggestion queries', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson(route('jobs.suggest', ['q' => str_repeat('a', 256)]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['q']);
});
