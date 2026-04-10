<?php

use App\Services\JobSearchFilters;

it('slugifies category and skills during normalization', function () {
    $filters = JobSearchFilters::normalize([
        'category' => 'Platform Engineering',
        'skills' => ['Laravel', 'React Native', ''],
    ]);

    expect($filters['category'])->toBe('platform-engineering')
        ->and($filters['skills'])->toBe(['laravel', 'react-native']);
});

it('slugifies category and skills when compacting search context', function () {
    $filters = JobSearchFilters::compact([
        'category' => 'Platform Engineering',
        'skills' => ['Laravel', 'React Native', ''],
        'page' => 0,
        'per_page' => 999,
    ]);

    expect($filters)->toBe([
        'category' => 'platform-engineering',
        'skills' => ['laravel', 'react-native'],
        'per_page' => 50,
    ]);
});
