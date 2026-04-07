<?php

use App\Contracts\SearchServiceInterface;
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

    $response = $this->actingAs($user)->get(route('jobs.show', 'lead-technical-architect'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('jobs/show')
        ->where('jobId', 'lead-technical-architect'),
    );
});

test('legacy search urls permanently redirect to canonical jobs urls', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/search?q=laravel&page=2')
        ->assertRedirect('/jobs?q=laravel&page=2');

    $this->actingAs($user)
        ->get('/search/jobs/lead-technical-architect')
        ->assertRedirect(route('jobs.show', 'lead-technical-architect'));
});
