<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests away from appearance settings', function () {
    $response = $this->get(route('appearance.edit'));

    $response->assertRedirect(route('login'));
});

it('renders the appearance settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('appearance.edit'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('settings/appearance'),
    );
});
