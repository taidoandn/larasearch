<?php

use App\Search\Builders\JobListingQueryBuilder;
use Tests\TestCase;

uses(TestCase::class);

it('builds job listing search request bodies without executing elasticsearch calls', function () {
    $body = (new JobListingQueryBuilder)->searchBody([
        'q' => 'laravel',
        'location' => ['da-nang'],
        'skills' => ['php', 'laravel'],
        'category' => ['platform-engineering'],
        'job_type' => ['full-time'],
        'work_model' => ['remote'],
        'experience_level' => ['senior'],
        'salary_min' => 1000,
        'salary_max' => 2500,
        'sort' => 'best_match',
        'page' => 2,
        'per_page' => 10,
    ]);

    $queryFilters = collect($body['query']['function_score']['query']['bool']['filter']);

    expect($body['from'])->toBe(10)
        ->and($body['size'])->toBe(10)
        ->and($body['highlight']['fields'])->toHaveKeys(['title', 'description'])
        ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'php']])
        ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
        ->and($body['aggs'])->toHaveKeys(['locations', 'categories', 'skills', 'job_types', 'work_models', 'experience_levels']);
});

it('builds a completion-backed suggestion request body', function () {
    $body = (new JobListingQueryBuilder)->suggestBody('lar');

    expect($body['size'])->toBe(0)
        ->and($body['suggest']['job_listing_suggest']['prefix'])->toBe('lar')
        ->and($body['suggest']['job_listing_suggest']['completion']['field'])->toBe('suggest')
        ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]]);
});
