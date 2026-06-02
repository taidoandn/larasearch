<?php

use App\Concerns\BuildsElasticsearchQueries;
use Tests\TestCase;

uses(TestCase::class);

class QueryBuilderHarness
{
    use BuildsElasticsearchQueries {
        matchAllQuery as public;
        keywordMustClause as public;
        anyTermsFilter as public;
        allTermFilters as public;
        rangeFilter as public;
        termsAggregation as public;
        labeledTermsAggregation as public;
        globalScopedAggregation as public;
    }
}

it('builds reusable elasticsearch query clauses', function () {
    $builder = new QueryBuilderHarness;

    expect($builder->matchAllQuery())->toEqual(['match_all' => (object) []])
        ->and($builder->keywordMustClause(''))->toEqual([
            ['match_all' => (object) []],
        ])
        ->and($builder->keywordMustClause('laravel', ['title^3'], ['fuzziness' => 'AUTO']))->toBe([
            [
                'multi_match' => [
                    'query' => 'laravel',
                    'fields' => ['title^3'],
                    'fuzziness' => 'AUTO',
                ],
            ],
        ])
        ->and($builder->anyTermsFilter('job_type', ['full-time', 'contract']))->toBe([
            'bool' => [
                'should' => [
                    ['term' => ['job_type' => 'full-time']],
                    ['term' => ['job_type' => 'contract']],
                ],
                'minimum_should_match' => 1,
            ],
        ])
        ->and($builder->allTermFilters('skill_slugs', ['laravel', 'php']))->toBe([
            ['term' => ['skill_slugs' => 'laravel']],
            ['term' => ['skill_slugs' => 'php']],
        ])
        ->and($builder->rangeFilter('salary_min', lte: 100000))->toBe([
            'range' => ['salary_min' => ['lte' => 100000]],
        ]);
});

it('builds reusable aggregation clauses', function () {
    $builder = new QueryBuilderHarness;

    expect($builder->termsAggregation('job_type', 10))->toBe([
        'terms' => ['field' => 'job_type', 'size' => 10],
    ])->and($builder->labeledTermsAggregation('skill_slugs', 'skills', 10))->toBe([
        'terms' => ['field' => 'skill_slugs', 'size' => 10],
        'aggs' => [
            'label' => [
                'top_hits' => [
                    '_source' => ['skill_slugs', 'skills'],
                    'size' => 1,
                ],
            ],
        ],
    ])->and($builder->globalScopedAggregation(
        ['bool' => ['filter' => [['term' => ['is_active' => true]]]]],
        $builder->termsAggregation('job_type', 10),
    ))->toEqual([
        'global' => (object) [],
        'aggs' => [
            'scope' => [
                'filter' => ['bool' => ['filter' => [['term' => ['is_active' => true]]]]],
                'aggs' => [
                    'values' => [
                        'terms' => ['field' => 'job_type', 'size' => 10],
                    ],
                ],
            ],
        ],
    ]);
});
