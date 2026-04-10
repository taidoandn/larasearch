<?php

use App\Services\ElasticsearchClient;
use App\Services\JobSuggestService;
use Tests\TestCase;

uses(TestCase::class);

it('returns normalized suggestions for partial keywords', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            expect($index)->toBe('job_listings_current')
                ->and($body['query']['bool']['must'][0]['multi_match']['query'])->toBe('lar')
                ->and($body['query']['bool']['must'][0]['multi_match']['fields'])->toContain('title.autocomplete^3')
                ->and($body['query']['bool']['must'][0]['multi_match']['fields'])->toContain('skills_text.autocomplete^2')
                ->and($body['query']['bool']['must'][0]['multi_match']['fuzziness'])->toBe('AUTO')
                ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
                ->and($body['query']['bool']['filter'][1])->toBe(['range' => ['published_at' => ['lte' => 'now']]])
                ->and($body['query']['bool']['filter'][2])->toBe([
                    'bool' => [
                        'should' => [
                            ['bool' => ['must_not' => [['exists' => ['field' => 'expires_at']]]]],
                            ['range' => ['expires_at' => ['gt' => 'now']]],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ]);

            return true;
        })
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'title' => 'Senior Laravel Backend Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel', 'PHP'],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new JobSuggestService($client))->suggest('lar');

    expect($results)->toBe([
        'items' => [
            ['label' => 'Senior Laravel Backend Engineer', 'type' => 'job_title'],
            ['label' => 'Laravel', 'type' => 'skill'],
        ],
    ]);
});

it('filters out suggestion labels that do not match the typed keyword', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('search')
        ->once()
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'title' => 'Senior Platform Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel', 'PHP'],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new JobSuggestService($client))->suggest('lar');

    expect($results)->toBe([
        'items' => [
            ['label' => 'Laravel', 'type' => 'skill'],
        ],
    ]);
});

it('keeps typo-tolerant suggestion matches when elasticsearch returns them', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('search')
        ->once()
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'title' => 'Senior Laravel Backend Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel', 'PHP'],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new JobSuggestService($client))->suggest('laravl');

    expect($results)->toBe([
        'items' => [
            ['label' => 'Senior Laravel Backend Engineer', 'type' => 'job_title'],
            ['label' => 'Laravel', 'type' => 'skill'],
        ],
    ]);
});

it('caps suggestions at five items', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            expect($index)->toBe('job_listings_current')
                ->and($body['size'])->toBe(15);

            return true;
        })
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'title' => 'Laravel One',
                            'company_name' => 'Laravel Labs',
                            'skills' => ['Laravel Alpha', 'Laravel Beta', 'Laravel Gamma', 'Laravel Delta'],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new JobSuggestService($client))->suggest('lar');

    expect($results['items'])->toHaveCount(5)
        ->and($results['items'])->toBe([
            ['label' => 'Laravel One', 'type' => 'job_title'],
            ['label' => 'Laravel Alpha', 'type' => 'skill'],
            ['label' => 'Laravel Beta', 'type' => 'skill'],
            ['label' => 'Laravel Gamma', 'type' => 'skill'],
            ['label' => 'Laravel Delta', 'type' => 'skill'],
        ]);
});

it('fetches enough hits to backfill unique suggestions after deduplication', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('search')
        ->once()
        ->withArgs(function (string $index, array $body): bool {
            expect($index)->toBe('job_listings_current')
                ->and($body['size'])->toBe(15);

            return true;
        })
        ->andReturn([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'title' => 'Laravel Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel'],
                        ],
                    ],
                    [
                        '_source' => [
                            'title' => 'Laravel Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel'],
                        ],
                    ],
                    [
                        '_source' => [
                            'title' => 'Laravel Platform Engineer',
                            'company_name' => 'Acme Tech',
                            'skills' => ['Laravel'],
                        ],
                    ],
                    [
                        '_source' => [
                            'title' => 'Laravel API Engineer',
                            'company_name' => 'Beta Tech',
                            'skills' => ['Laravel Nova'],
                        ],
                    ],
                    [
                        '_source' => [
                            'title' => 'Laravel Infrastructure Engineer',
                            'company_name' => 'Gamma Tech',
                            'skills' => ['Laravel Vapor'],
                        ],
                    ],
                ],
            ],
        ]);

    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $results = (new JobSuggestService($client))->suggest('lar');

    expect($results['items'])->toBe([
        ['label' => 'Laravel Engineer', 'type' => 'job_title'],
        ['label' => 'Laravel', 'type' => 'skill'],
        ['label' => 'Laravel Platform Engineer', 'type' => 'job_title'],
        ['label' => 'Laravel API Engineer', 'type' => 'job_title'],
        ['label' => 'Laravel Nova', 'type' => 'skill'],
    ]);
});

it('returns no suggestions for empty keywords', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldNotReceive('search');

    expect((new JobSuggestService($client))->suggest('   '))->toBe([
        'items' => [],
    ]);
});
