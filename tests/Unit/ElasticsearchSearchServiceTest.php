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
                ->and($body['aggs']['skills']['aggs']['label']['top_hits']['_source'])->toBe(['skill_slugs', 'skills'])
                ->and($body['query']['function_score']['query']['bool']['must'])->toEqual([['match_all' => (object) []]])
                ->and($body['query']['function_score']['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
                ->and($body['query']['function_score']['functions'][1]['exp']['published_at']['scale'])->toBe('7d')
                ->and($body['sort'][0])->toBe(['published_at' => ['order' => 'desc']])
                ->and($body['sort'][1])->toBe(['id' => ['order' => 'desc']]);

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
            'from' => 0,
            'to' => 0,
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
                            'application_url' => 'https://jobs.example.test/apply/senior-laravel-backend-engineer',
                            'company_name' => 'Acme Tech',
                            'company_slug' => 'acme-tech',
                            'company_website' => 'https://acme.example.test',
                            'location_slugs' => ['da-nang'],
                            'location_labels' => ['Da Nang'],
                            'skill_slugs' => ['laravel', 'php'],
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
                        [
                            'key' => 'da-nang',
                            'doc_count' => 1,
                            'label' => [
                                'hits' => [
                                    'hits' => [
                                        [
                                            '_source' => [
                                                'location_labels' => ['Da Nang'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new ElasticsearchSearchService($client))->search([
        'q' => 'laravel',
        'location' => 'da-nang',
        'skills' => ['laravel'],
        'sort' => 'best_match',
    ]);

    expect($results['items'][0])->toBe([
        'id' => 10,
        'slug' => 'senior-laravel-backend-engineer',
        'title' => 'Senior Laravel Backend Engineer',
        'description' => '',
        'application_url' => 'https://jobs.example.test/apply/senior-laravel-backend-engineer',
        'company' => [
            'name' => 'Acme Tech',
            'slug' => 'acme-tech',
            'website' => 'https://acme.example.test',
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
        ->and($results['pagination']['from'])->toBe(1)
        ->and($results['pagination']['to'])->toBe(1)
        ->and($results['facets']['locations'])->toBe([
            ['value' => 'da-nang', 'label' => 'Da Nang', 'count' => 1],
        ])
        ->and($results['sort'])->toBe('best_match');
});

it('matches facet labels to the correct array bucket keys', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->andReturn([
            'hits' => [
                'total' => ['value' => 0],
                'hits' => [],
            ],
            'aggregations' => [
                'skills' => [
                    'buckets' => [
                        [
                            'key' => 'typescript',
                            'doc_count' => 12,
                            'label' => [
                                'hits' => [
                                    'hits' => [
                                        [
                                            '_source' => [
                                                'skill_slugs' => ['docker', 'typescript', 'mysql'],
                                                'skills' => ['Docker', 'TypeScript', 'MySQL'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'key' => 'mysql',
                            'doc_count' => 7,
                            'label' => [
                                'hits' => [
                                    'hits' => [
                                        [
                                            '_source' => [
                                                'skill_slugs' => ['docker', 'typescript', 'mysql'],
                                                'skills' => ['Docker', 'TypeScript', 'MySQL'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new ElasticsearchSearchService($client))->search([
        'q' => '',
        'page' => 1,
        'per_page' => 20,
        'sort' => 'best_match',
    ]);

    expect($results['facets']['skills'])->toBe([
        ['value' => 'typescript', 'label' => 'TypeScript', 'count' => 12],
        ['value' => 'mysql', 'label' => 'MySQL', 'count' => 7],
    ]);
});

it('matches human-readable locations and requires all selected skills in elasticsearch filters', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            expect($index)->toBe('job_listings_current')
                ->and($body['query']['function_score']['query']['bool']['filter'])->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($body['query']['function_score']['query']['bool']['filter'])->toContain(['term' => ['skill_slugs' => 'php']]);

            $locationFilter = collect($body['query']['function_score']['query']['bool']['filter'])
                ->first(function (array $filter): bool {
                    if (! array_key_exists('bool', $filter) || ! array_key_exists('should', $filter['bool'])) {
                        return false;
                    }

                    return collect($filter['bool']['should'])->contains(function (array $condition): bool {
                        return array_key_exists('term', $condition)
                            && ($condition['term']['location_slugs'] ?? null) === 'st-johns';
                    });
                });

            expect($locationFilter)->toBe([
                'bool' => [
                    'should' => [
                        ['term' => ['location_slugs' => 'st-johns']],
                        ['wildcard' => ['location_labels' => ['value' => "*St. John's*", 'case_insensitive' => true]]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]);

            $skillTerms = collect($body['query']['function_score']['query']['bool']['filter'])
                ->filter(fn (array $filter): bool => array_key_exists('term', $filter) && array_key_exists('skill_slugs', $filter['term']))
                ->values()
                ->all();

            expect($skillTerms)->toHaveCount(2)
                ->and($body['query']['function_score']['query']['bool']['filter'])->not->toContain(['terms' => ['skill_slugs' => ['laravel', 'php']]]);

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
        'location' => "St. John's",
        'skills' => ['laravel', 'php'],
        'sort' => 'best_match',
        'page' => 1,
        'per_page' => 20,
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('keeps one-sided salary ranges searchable when overlap filters are applied', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $minimumSalaryFilter = [
                'bool' => [
                    'should' => [
                        ['range' => ['salary_max' => ['gte' => 100000]]],
                        [
                            'bool' => [
                                'must' => [
                                    ['bool' => ['must_not' => [['exists' => ['field' => 'salary_max']]]]],
                                    ['range' => ['salary_min' => ['gte' => 100000]]],
                                ],
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];

            $maximumSalaryFilter = [
                'bool' => [
                    'should' => [
                        ['range' => ['salary_min' => ['lte' => 140000]]],
                        [
                            'bool' => [
                                'must' => [
                                    ['bool' => ['must_not' => [['exists' => ['field' => 'salary_min']]]]],
                                    ['range' => ['salary_max' => ['lte' => 140000]]],
                                ],
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];

            expect($index)->toBe('job_listings_current')
                ->and($body['query']['function_score']['query']['bool']['filter'])->toContain($minimumSalaryFilter)
                ->and($body['query']['function_score']['query']['bool']['filter'])->toContain($maximumSalaryFilter)
                ->and($body['query']['function_score']['query']['bool']['filter'])->not->toContain(['range' => ['salary_min' => ['gte' => 100000]]])
                ->and($body['query']['function_score']['query']['bool']['filter'])->not->toContain(['range' => ['salary_max' => ['lte' => 140000]]]);

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
        'salary_min' => 100000,
        'salary_max' => 140000,
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});
