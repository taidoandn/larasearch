<?php

use App\Services\ElasticsearchClient;
use App\Services\ElasticsearchSearchService;
use Tests\TestCase;

uses(TestCase::class);

it('builds a filtered browse query with canonical defaults for an empty query', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            expect($index)->toBe('job_listings_current')
                ->and($body['from'])->toBe(20)
                ->and($body['size'])->toBe(20)
                ->and($body['query']['bool']['must'])->toEqual([['match_all' => (object) []]])
                ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
                ->and($body['sort'][0])->toBe(['published_at' => ['order' => 'desc']]);

            return true;
        })
        ->andReturn([
            'hits' => [
                'total' => ['value' => 0],
                'hits' => [],
            ],
            'aggregations' => [],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new ElasticsearchSearchService($client))->search([
        'q' => '',
        'page' => 2,
        'per_page' => 20,
        'sort' => 'newest',
    ]);

    expect($results)->toBe([
        'items' => [],
        'pagination' => [
            'page' => 2,
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
        'sort' => 'newest',
    ]);
});

it('normalizes elasticsearch hits into the canonical search contract', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->andReturn([
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    [
                        '_source' => [
                            'id' => '10',
                            'slug' => 'senior-laravel-backend-engineer',
                            'title' => 'Senior Laravel Backend Engineer',
                            'company_name' => 'Acme Tech',
                            'company_slug' => 'acme-tech',
                            'locations' => ['da-nang'],
                            'location_labels' => ['Da Nang'],
                            'skills' => ['Laravel', 'PHP'],
                            'job_type' => 'full-time',
                            'work_model' => 'hybrid',
                            'experience_level' => 'senior',
                            'salary_min' => 1500,
                            'salary_max' => 2500,
                            'salary_currency' => 'USD',
                            'salary_is_visible' => true,
                            'published_at' => '2026-04-01T09:00:00Z',
                        ],
                        'highlight' => [
                            'title' => ['<em>Senior Laravel</em> Backend Engineer'],
                        ],
                    ],
                ],
            ],
            'aggregations' => [
                'locations' => [
                    'buckets' => [
                        ['key' => 'da-nang', 'doc_count' => 1],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new ElasticsearchSearchService($client))->search([
        'q' => 'laravel',
        'sort' => 'best_match',
    ]);

    expect($results['items'][0])->toBe([
        'id' => '10',
        'slug' => 'senior-laravel-backend-engineer',
        'title' => 'Senior Laravel Backend Engineer',
        'company' => [
            'name' => 'Acme Tech',
            'slug' => 'acme-tech',
        ],
        'primary_location' => 'Da Nang',
        'locations' => ['Da Nang'],
        'skills' => ['Laravel', 'PHP'],
        'salary' => [
            'min' => 1500,
            'max' => 2500,
            'currency' => 'USD',
            'is_visible' => true,
        ],
        'job_type' => 'full-time',
        'work_model' => 'hybrid',
        'experience_level' => 'senior',
        'published_at' => '2026-04-01T09:00:00Z',
        'highlight' => [
            'title' => '<em>Senior Laravel</em> Backend Engineer',
            'description' => null,
        ],
    ])
        ->and($results['pagination']['total'])->toBe(1)
        ->and($results['facets']['locations'])->toBe([
            ['value' => 'da-nang', 'count' => 1],
        ])
        ->and($results['sort'])->toBe('best_match');
});
