<?php

use App\Contracts\SearchServiceInterface;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page for search results', function () {
    $response = $this->get(route('larasearch.search-results'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the search results page', function () {
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

    $response = $this->actingAs($user)->get(route('larasearch.search-results'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('search-results'),
    );
});

test('authenticated users can visit the job detail page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('larasearch.job-detail', 'lead-technical-architect'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('job-detail')
        ->where('jobId', 'lead-technical-architect'),
    );
});
