<?php

use App\Services\JobListingSearchIndex;
use Tests\TestCase;

uses(TestCase::class);

it('returns the configured job listings alias', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_test_alias');

    expect(app(JobListingSearchIndex::class)->alias())->toBe('job_listings_test_alias');
});

it('builds the shared job listing visibility filters', function () {
    expect(app(JobListingSearchIndex::class)->visibilityFilters())->toBe([
        ['term' => ['is_active' => true]],
        ['range' => ['published_at' => ['lte' => 'now']]],
        [
            'bool' => [
                'should' => [
                    ['bool' => ['must_not' => [['exists' => ['field' => 'expires_at']]]]],
                    ['range' => ['expires_at' => ['gt' => 'now']]],
                ],
                'minimum_should_match' => 1,
            ],
        ],
    ]);
});

it('builds a match all clause for empty keywords', function () {
    expect(app(JobListingSearchIndex::class)->keywordMustClause(''))->toEqual([
        ['match_all' => (object) []],
    ]);
});

it('builds configurable multi match clauses for typed keywords', function () {
    expect(app(JobListingSearchIndex::class)->keywordMustClause('laravel', [
        'fields' => ['title^3', 'skills_text^2'],
        'fuzziness' => 'AUTO',
        'type' => 'best_fields',
    ]))->toBe([
        [
            'multi_match' => [
                'query' => 'laravel',
                'fields' => ['title^3', 'skills_text^2'],
                'fuzziness' => 'AUTO',
                'type' => 'best_fields',
            ],
        ],
    ]);
});
