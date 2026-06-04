<?php

use App\Search\Utils\SearchResponseFormatter;
use Tests\TestCase;

uses(TestCase::class);

it('formats elasticsearch totals and paginator-compatible arrays', function () {
    $formatter = new SearchResponseFormatter;

    $results = $formatter->lengthAwareResults(
        items: [['id' => 1], ['id' => 2]],
        total: 22,
        page: 2,
        perPage: 10,
        extra: ['facets' => ['skills' => []], 'sort' => 'newest'],
    );

    expect($formatter->totalHits(['hits' => ['total' => ['value' => 22]]]))->toBe(22)
        ->and($results['data'])->toBe([['id' => 1], ['id' => 2]])
        ->and($results['current_page'])->toBe(2)
        ->and($results['per_page'])->toBe(10)
        ->and($results['from'])->toBe(11)
        ->and($results['to'])->toBe(12)
        ->and($results['last_page'])->toBe(3)
        ->and($results['facets'])->toBe(['skills' => []])
        ->and($results['sort'])->toBe('newest');
});

it('formats labeled facet buckets', function () {
    $formatter = new SearchResponseFormatter;

    $facets = $formatter->facetItems([
        [
            'key' => 'typescript',
            'doc_count' => 12,
            'label' => [
                'hits' => [
                    'hits' => [
                        [
                            '_source' => [
                                'skill_slugs' => ['docker', 'typescript'],
                                'skills' => ['Docker', 'TypeScript'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], 'skills', 'skill_slugs');

    expect($facets)->toBe([
        ['value' => 'typescript', 'label' => 'TypeScript', 'count' => 12],
    ]);
});
