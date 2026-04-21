<?php

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
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
                ->and($body['aggs']['skills']['aggs']['scope']['aggs']['values']['aggs']['label']['top_hits']['_source'])->toBe(['skill_slugs', 'skills'])
                ->and($body['query']['bool']['must'])->toEqual([['match_all' => (object) []]])
                ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
                ->and($body['query'])->not->toHaveKey('function_score')
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
        'location' => ['da-nang'],
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
            'logo_url' => null,
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
        'job_type_label' => JobType::FULL_TIME->label(),
        'work_model' => 'hybrid',
        'work_model_label' => WorkModel::HYBRID->label(),
        'experience_level' => 'senior',
        'experience_level_label' => ExperienceLevel::SENIOR->label(),
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

it('normalizes scoped facet aggregations from elasticsearch response payloads', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->andReturn([
            'hits' => [
                'total' => ['value' => 0],
                'hits' => [],
            ],
            'aggregations' => [
                'locations' => [
                    'scope' => [
                        'doc_count' => 2,
                        'values' => [
                            'buckets' => [
                                [
                                    'key' => 'da-nang',
                                    'doc_count' => 2,
                                    'label' => [
                                        'hits' => [
                                            'hits' => [
                                                [
                                                    '_source' => [
                                                        'location_slugs' => ['da-nang'],
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
                ],
                'categories' => [
                    'scope' => [
                        'doc_count' => 1,
                        'values' => [
                            'buckets' => [
                                [
                                    'key' => 'platform-engineering',
                                    'doc_count' => 1,
                                    'label' => [
                                        'hits' => [
                                            'hits' => [
                                                [
                                                    '_source' => [
                                                        'category_slugs' => ['platform-engineering'],
                                                        'category_names' => ['Platform Engineering'],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'job_types' => [
                    'scope' => [
                        'doc_count' => 1,
                        'values' => [
                            'buckets' => [
                                [
                                    'key' => 'full-time',
                                    'doc_count' => 1,
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
        'sort' => 'best_match',
    ]);

    expect($results['facets']['locations'])->toBe([
        ['value' => 'da-nang', 'label' => 'Da Nang', 'count' => 2],
    ])->and($results['facets']['categories'])->toBe([
        ['value' => 'platform-engineering', 'label' => 'Platform Engineering', 'count' => 1],
    ])->and($results['facets']['job_types'])->toBe([
        ['value' => 'full-time', 'label' => 'full-time', 'count' => 1],
    ]);
});

it('matches any selected locations and requires all selected skills in elasticsearch filters', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter']);
            $categoryFacetFilters = collect($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'php']])
                ->and($queryFilters)->toContain(['term' => ['is_active' => true]])
                ->and($body['aggs']['categories']['aggs']['scope']['filter']['bool']['must'])->toHaveCount(1)
                ->and(array_keys($body['aggs']['categories']['aggs']['scope']['filter']['bool']['must'][0]))->toBe(['match_all']);

            $locationFilter = $queryFilters->first(function (array $filter): bool {
                if (! array_key_exists('bool', $filter) || ! array_key_exists('should', $filter['bool'])) {
                    return false;
                }

                return collect($filter['bool']['should'])->contains(function (array $condition): bool {
                    return array_key_exists('term', $condition)
                        && ($condition['term']['location_slugs'] ?? null) === 'st-johns';
                });
            });

            $locationAggregationFilter = collect($body['aggs']['locations']['aggs']['scope']['filter']['bool']['filter'] ?? [])
                ->first(function (array $filter): bool {
                    if (! array_key_exists('bool', $filter) || ! array_key_exists('should', $filter['bool'])) {
                        return false;
                    }

                    return collect($filter['bool']['should'])->contains(function (array $condition): bool {
                        return array_key_exists('term', $condition)
                            && array_key_exists('location_slugs', $condition['term']);
                    });
                });

            expect($locationFilter)->toBe([
                'bool' => [
                    'should' => [
                        ['term' => ['location_slugs' => 'st-johns']],
                        ['term' => ['location_slugs' => 'da-nang']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])
                ->and($locationAggregationFilter)->toBeNull()
                ->and($categoryFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($categoryFacetFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($categoryFacetFilters)->toContain(['term' => ['skill_slugs' => 'php']]);

            $categoryLocationFilter = $categoryFacetFilters->first(function (array $filter): bool {
                if (! array_key_exists('bool', $filter) || ! array_key_exists('should', $filter['bool'])) {
                    return false;
                }

                return collect($filter['bool']['should'])->contains(function (array $condition): bool {
                    return array_key_exists('term', $condition)
                        && ($condition['term']['location_slugs'] ?? null) === 'st-johns';
                });
            });

            expect($categoryLocationFilter)->toBe([
                'bool' => [
                    'should' => [
                        ['term' => ['location_slugs' => 'st-johns']],
                        ['term' => ['location_slugs' => 'da-nang']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]);

            $skillTerms = $queryFilters
                ->filter(fn (array $filter): bool => array_key_exists('term', $filter) && array_key_exists('skill_slugs', $filter['term']))
                ->values()
                ->all();

            expect($skillTerms)->toHaveCount(2)
                ->and($queryFilters)->not->toContain(['terms' => ['skill_slugs' => ['laravel', 'php']]]);

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
        'location' => ["St. John's", 'Da Nang'],
        'skills' => ['laravel', 'php'],
        'sort' => 'best_match',
        'page' => 1,
        'per_page' => 20,
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('matches any selected work models and experience levels within each facet group', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? [])
                ->filter(fn (array $filter): bool => array_key_exists('bool', $filter) && array_key_exists('should', $filter['bool']))
                ->values();
            $workModelFacetFilters = collect($body['aggs']['work_models']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $experienceFacetFilters = collect($body['aggs']['experience_levels']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $jobTypeFacetFilters = collect($body['aggs']['job_types']['aggs']['scope']['filter']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['work_model' => 'remote']],
                            ['term' => ['work_model' => 'hybrid']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['experience_level' => 'mid']],
                            ['term' => ['experience_level' => 'senior']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($body)->not->toHaveKey('post_filter')
                ->and($workModelFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($experienceFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($jobTypeFacetFilters)->toContain(['term' => ['is_active' => true]]);

            expect($workModelFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['experience_level' => 'mid']],
                        ['term' => ['experience_level' => 'senior']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($experienceFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['work_model' => 'remote']],
                        ['term' => ['work_model' => 'hybrid']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($jobTypeFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['work_model' => 'remote']],
                        ['term' => ['work_model' => 'hybrid']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($jobTypeFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['experience_level' => 'mid']],
                        ['term' => ['experience_level' => 'senior']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]);

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
        'work_model' => ['remote', 'hybrid'],
        'experience_level' => ['mid', 'senior'],
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('matches any selected categories and job types within each facet group', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? [])
                ->filter(fn (array $filter): bool => array_key_exists('bool', $filter) && array_key_exists('should', $filter['bool']))
                ->values();
            $categoryFacetFilters = collect($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $jobTypeFacetFilters = collect($body['aggs']['job_types']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $workModelFacetFilters = collect($body['aggs']['work_models']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $experienceFacetFilters = collect($body['aggs']['experience_levels']['aggs']['scope']['filter']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['category_slugs' => 'platform-engineering']],
                            ['term' => ['category_slugs' => 'developer-tools']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['job_type' => 'full-time']],
                            ['term' => ['job_type' => 'contract']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($body)->not->toHaveKey('post_filter')
                ->and($categoryFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($jobTypeFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($workModelFacetFilters)->toContain(['term' => ['is_active' => true]])
                ->and($experienceFacetFilters)->toContain(['term' => ['is_active' => true]]);

            expect($categoryFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['job_type' => 'full-time']],
                        ['term' => ['job_type' => 'contract']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($jobTypeFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['category_slugs' => 'platform-engineering']],
                        ['term' => ['category_slugs' => 'developer-tools']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($workModelFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['category_slugs' => 'platform-engineering']],
                        ['term' => ['category_slugs' => 'developer-tools']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($workModelFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['job_type' => 'full-time']],
                        ['term' => ['job_type' => 'contract']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($experienceFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['category_slugs' => 'platform-engineering']],
                        ['term' => ['category_slugs' => 'developer-tools']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ])->and($experienceFacetFilters)->toContain([
                'bool' => [
                    'should' => [
                        ['term' => ['job_type' => 'full-time']],
                        ['term' => ['job_type' => 'contract']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ]);

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
        'category' => ['platform-engineering', 'developer-tools'],
        'job_type' => ['full-time', 'contract'],
        'sort' => 'best_match',
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
                ->and($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'])->toContain($minimumSalaryFilter)
                ->and($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'])->toContain($maximumSalaryFilter)
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

it('excludes active skills from only the skills aggregation scope', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $skillsFacetFilters = collect($body['aggs']['skills']['aggs']['scope']['filter']['bool']['filter'] ?? []);
            $categoriesFacetFilters = collect($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($body['query']['function_score']['query']['bool']['filter'])->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($categoriesFacetFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($skillsFacetFilters)->not->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($skillsFacetFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['location_slugs' => 'da-nang']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

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
        'skills' => ['laravel'],
        'location' => ['Da Nang'],
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('preserves scalar enum filter inputs at the search service boundary', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['category_slugs' => 'platform-engineering']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['job_type' => 'full-time']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['work_model' => 'remote']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['experience_level' => 'senior']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

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
        'category' => 'platform-engineering',
        'job_type' => 'full-time',
        'work_model' => 'remote',
        'experience_level' => 'senior',
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('preserves scalar location and skills inputs at the search service boundary', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
                ->and($queryFilters)->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['location_slugs' => 'da-nang']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

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
        'location' => 'Da Nang',
        'skills' => 'laravel',
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('ignores blank scalar location and skills inputs', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->not->toContain(['term' => ['skill_slugs' => '']])
                ->and($queryFilters)->not->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['location_slugs' => '']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

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
        'location' => '   ',
        'skills' => '',
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});

it('ignores blank scalar enum filter inputs', function () {
    $client = Mockery::mock(ElasticsearchClient::class);

    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            $queryFilters = collect($body['query']['function_score']['query']['bool']['filter'] ?? []);

            expect($index)->toBe('job_listings_current')
                ->and($queryFilters)->not->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['category_slugs' => '']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ])
                ->and($queryFilters)->not->toContain([
                    'bool' => [
                        'should' => [
                            ['term' => ['job_type' => '']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

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
        'category' => '   ',
        'job_type' => '',
        'sort' => 'best_match',
    ]);

    expect($results['pagination']['total'])->toBe(0);
});
