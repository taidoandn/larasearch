<?php

use App\Search\Searchers\JobListingSearcher;
use Tests\TestCase;

uses(TestCase::class);

it('returns normalized suggestions for partial keywords', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $client = fakeElasticsearchClient([
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
    ], $http);

    $results = (new JobListingSearcher($client))->suggest('lar');
    $body = $http->jsonBody();

    expect((string) $http->requests[0]->getUri())->toContain('/job_listings_current/_search')
        ->and($body['size'])->toBe(5)
        ->and($body['_source'])->toBe(['title', 'company_name', 'skills'])
        ->and($body)->not->toHaveKey('suggest')
        ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
        ->and($body['query']['bool']['must'][0])->toBe([
            'multi_match' => [
                'query' => 'lar',
                'fields' => [
                    'title.autocomplete^3',
                    'skills_text.autocomplete^2',
                    'company_name.autocomplete^2',
                ],
                'type' => 'bool_prefix',
            ],
        ])
        ->and($results)->toBe([
            'items' => [
                ['label' => 'Senior Laravel Backend Engineer', 'type' => 'job_title'],
                ['label' => 'Laravel', 'type' => 'skill'],
            ],
        ]);
});

it('filters out suggestion labels that do not match and caps unique results', function () {
    config()->set('elasticsearch.aliases.job_listings', 'job_listings_current');

    $client = fakeElasticsearchClient([
        'hits' => [
            'hits' => [
                [
                    '_source' => [
                        'title' => 'Senior Platform Engineer',
                        'company_name' => 'Acme Tech',
                        'skills' => ['Laravel', 'PHP'],
                    ],
                ],
                [
                    '_source' => [
                        'title' => 'Laravel Engineer',
                        'company_name' => 'Laravel Labs',
                        'skills' => ['Laravel Alpha', 'Laravel Beta', 'Laravel Gamma', 'Laravel Delta'],
                    ],
                ],
            ],
        ],
    ], $http);

    $results = (new JobListingSearcher($client))->suggest('lar');

    expect($results['items'])->toHaveCount(5)
        ->and($results['items'])->toBe([
            ['label' => 'Laravel', 'type' => 'skill'],
            ['label' => 'Laravel Engineer', 'type' => 'job_title'],
            ['label' => 'Laravel Alpha', 'type' => 'skill'],
            ['label' => 'Laravel Beta', 'type' => 'skill'],
            ['label' => 'Laravel Gamma', 'type' => 'skill'],
        ]);
});

it('returns no suggestions for empty keywords', function () {
    $client = fakeElasticsearchClient([], $http);

    expect((new JobListingSearcher($client))->suggest('   '))->toBe([
        'items' => [],
    ])->and($http->requests)->toBe([]);
});
