<?php

use App\Services\JobSearchFilters;

it('slugifies category and skills during normalization', function () {
    $filters = JobSearchFilters::normalize([
        'category' => ['Platform Engineering', 'Developer Tools', 'Platform Engineering'],
        'skills' => ['Laravel', 'React Native', ''],
    ]);

    expect($filters['category'])->toBe(['platform-engineering', 'developer-tools'])
        ->and($filters['skills'])->toBe(['laravel', 'react-native']);
});

it('slugifies category and skills when compacting search context', function () {
    $filters = JobSearchFilters::compact([
        'category' => ['Platform Engineering', 'Developer Tools'],
        'skills' => ['Laravel', 'React Native', ''],
        'page' => 0,
        'per_page' => 999,
    ]);

    expect($filters)->toBe([
        'category' => ['platform-engineering', 'developer-tools'],
        'skills' => ['laravel', 'react-native'],
        'per_page' => 50,
    ]);
});

it('normalizes multi-select categories and job types', function () {
    $filters = JobSearchFilters::normalize([
        'category' => ['Platform Engineering', 'Developer Tools', ''],
        'job_type' => ['full-time', 'contract', 'temporary', 'contract'],
    ]);

    expect($filters['category'])->toBe(['platform-engineering', 'developer-tools'])
        ->and($filters['job_type'])->toBe(['full-time', 'contract']);
});

it('keeps normalized category and job type, work model and experience level filters when compacting search context', function () {
    $filters = JobSearchFilters::compact([
        'category' => ['Platform Engineering', 'Developer Tools', 'Platform Engineering'],
        'job_type' => ['full-time', 'temporary', 'contract'],
        'work_model' => ['remote'],
        'experience_level' => ['senior'],
        'skills' => ['Laravel'],
    ]);

    expect($filters)->toBe([
        'category' => ['platform-engineering', 'developer-tools'],
        'skills' => ['laravel'],
        'job_type' => ['full-time', 'contract'],
        'work_model' => ['remote'],
        'experience_level' => ['senior'],
    ]);
});

it('normalizes multi-select work model and experience level filters', function () {
    $filters = JobSearchFilters::normalize([
        'work_model' => ['remote', 'hybrid', ''],
        'experience_level' => ['senior', 'mid', 'invalid-level'],
    ]);

    expect($filters['work_model'])->toBe(['remote', 'hybrid'])
        ->and($filters['experience_level'])->toBe(['senior', 'mid']);
});

it('keeps normalized multi-select filters when compacting search context', function () {
    $filters = JobSearchFilters::compact([
        'work_model' => ['remote', 'hybrid', 'remote'],
        'experience_level' => ['senior', 'principal'],
        'page' => 1,
        'per_page' => 20,
    ]);

    expect($filters)->toBe([
        'work_model' => ['remote', 'hybrid'],
        'experience_level' => ['senior'],
    ]);
});
