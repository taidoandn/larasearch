<?php

use App\Contracts\SearchServiceInterface;
use App\Models\User;

it('renders the jobs index page with canonical job props', function () {
    $user = User::factory()->create();

    $searchService = Mockery::mock(SearchServiceInterface::class);
    $searchService->shouldReceive('search')
        ->once()
        ->with([
            'q' => 'laravel',
            'location' => 'da-nang',
            'category' => '',
            'skills' => [],
            'job_type' => '',
            'work_model' => '',
            'experience_level' => '',
            'salary_min' => null,
            'salary_max' => null,
            'sort' => 'newest',
            'page' => 2,
            'per_page' => 10,
        ])
        ->andReturn([
            'items' => [
                [
                    'id' => 1,
                    'slug' => 'senior-laravel-backend-engineer',
                    'title' => 'Senior Laravel Backend Engineer',
                    'company' => [
                        'name' => 'Acme Tech',
                        'slug' => 'acme-tech',
                    ],
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
            'pagination' => [
                'page' => 2,
                'per_page' => 10,
                'total' => 42,
                'total_pages' => 5,
                'has_more' => true,
            ],
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

    app()->instance(SearchServiceInterface::class, $searchService);

    $response = $this->actingAs($user)->get(route('jobs.index', [
        'q' => 'laravel',
        'location' => 'da-nang',
        'sort' => 'newest',
        'page' => 2,
        'per_page' => 10,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('jobs/index')
        ->where('results.pagination.page', 2)
        ->where('results.pagination.per_page', 10)
        ->where('results.sort', 'newest')
        ->where('filters.q', 'laravel')
        ->where('filters.location', 'da-nang')
        ->where('filters.sort', 'newest')
        ->where('filters.page', 2)
        ->where('filters.per_page', 10),
    );
});

it('rejects invalid enum-backed search filters', function () {
    $user = User::factory()->create();

    $response = $this->from(route('jobs.index'))
        ->actingAs($user)
        ->get(route('jobs.index', [
            'job_type' => 'temporary',
            'work_model' => 'remote-first',
            'experience_level' => 'principal',
        ]));

    $response->assertRedirect(route('jobs.index'));
    $response->assertSessionHasErrors([
        'job_type',
        'work_model',
        'experience_level',
    ]);
});
