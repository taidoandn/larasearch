<?php

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Searchers\JobListingSearcher;
use Tests\TestCase;

uses(TestCase::class);

it('builds a filtered browse query and returns length aware paginator output', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $client = fakeElasticsearchClient([
        'hits' => [
            'total' => ['value' => 0],
            'hits' => [],
        ],
        'aggregations' => [],
    ], $http);

    $results = (new JobListingSearcher($client))->search([
        'q' => '',
        'page' => 2,
        'per_page' => 20,
        'sort' => 'newest',
    ]);

    $body = $http->jsonBody();

    expect((string) $http->requests[0]->getUri())->toContain('/job_listings_current/_search')
        ->and($body['from'])->toBe(20)
        ->and($body['size'])->toBe(20)
        ->and($body['aggs']['skills']['aggs']['scope']['aggs']['values']['aggs']['label']['top_hits']['_source'])->toBe(['skill_slugs', 'skills'])
        ->and($body['query']['bool']['must'])->toBe([['match_all' => []]])
        ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
        ->and($body['query'])->not->toHaveKey('function_score')
        ->and($body['sort'][0])->toBe(['published_at' => ['order' => 'desc']])
        ->and($body['sort'][1])->toBe(['id' => ['order' => 'desc']])
        ->and($results['data'])->toBe([])
        ->and($results['current_page'])->toBe(2)
        ->and($results['per_page'])->toBe(20)
        ->and($results['total'])->toBe(0)
        ->and($results['from'])->toBeNull()
        ->and($results['to'])->toBeNull()
        ->and($results['last_page'])->toBe(1)
        ->and($results['facets'])->toBe([
            'locations' => [],
            'categories' => [],
            'skills' => [],
            'job_types' => [],
            'work_models' => [],
            'experience_levels' => [],
        ])
        ->and($results['sort'])->toBe('newest');
});

it('normalizes elasticsearch hits into the canonical job listing result items', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $client = fakeElasticsearchClient([
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
    ], $http);

    $results = (new JobListingSearcher($client))->search([
        'q' => 'laravel',
        'location' => ['da-nang'],
        'skills' => ['laravel'],
        'sort' => 'best_match',
    ]);

    expect($results['data'][0])->toBe([
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
    ])->and($results['total'])->toBe(1)
        ->and($results['from'])->toBe(1)
        ->and($results['to'])->toBe(1)
        ->and($results['facets']['locations'])->toBe([
            ['value' => 'da-nang', 'label' => 'Da Nang', 'count' => 1],
        ]);
});

it('keeps selected filters scoped correctly for results and facets', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $client = fakeElasticsearchClient([
        'hits' => [
            'total' => ['value' => 0],
            'hits' => [],
        ],
        'aggregations' => [],
    ], $http);

    (new JobListingSearcher($client))->search([
        'location' => ["St. John's", 'Da Nang'],
        'skills' => ['laravel', 'php'],
        'category' => ['platform-engineering'],
        'job_type' => ['full-time', 'contract'],
        'salary_min' => 100000,
        'salary_max' => 140000,
        'sort' => 'best_match',
    ]);

    $body = $http->jsonBody();
    $queryFilters = collect($body['query']['function_score']['query']['bool']['filter']);
    $skillsFacetFilters = collect($body['aggs']['skills']['aggs']['scope']['filter']['bool']['filter'] ?? []);
    $locationsFacetFilters = collect($body['aggs']['locations']['aggs']['scope']['filter']['bool']['filter'] ?? []);
    $categoriesFacetFilters = collect($body['aggs']['categories']['aggs']['scope']['filter']['bool']['filter'] ?? []);

    expect($queryFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
        ->and($queryFilters)->toContain(['term' => ['skill_slugs' => 'php']])
        ->and($queryFilters)->not->toContain(['terms' => ['skill_slugs' => ['laravel', 'php']]])
        ->and($skillsFacetFilters)->not->toContain(['term' => ['skill_slugs' => 'laravel']])
        ->and($locationsFacetFilters)->not->toContain([
            'bool' => [
                'should' => [
                    ['term' => ['location_slugs' => 'st-johns']],
                    ['term' => ['location_slugs' => 'da-nang']],
                ],
                'minimum_should_match' => 1,
            ],
        ])
        ->and($categoriesFacetFilters)->toContain(['term' => ['skill_slugs' => 'laravel']])
        ->and($queryFilters)->toContain([
            'bool' => [
                'should' => [
                    ['term' => ['location_slugs' => 'st-johns']],
                    ['term' => ['location_slugs' => 'da-nang']],
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
        ]);
});
