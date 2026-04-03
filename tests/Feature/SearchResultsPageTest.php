<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page for search results', function () {
    $response = $this->get(route('larasearch.search-results'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the search results page', function () {
    $user = User::factory()->create();

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
