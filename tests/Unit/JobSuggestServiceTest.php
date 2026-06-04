<?php

use App\Searchers\JobListingSearcher;
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
        ->and($body['query']['bool']['must'][0]['multi_match']['query'])->toBe('lar')
        ->and($body['query']['bool']['must'][0]['multi_match']['fields'])->toContain('title.autocomplete^3')
        ->and($body['query']['bool']['must'][0]['multi_match']['fuzziness'])->toBe('AUTO')
        ->and($body['query']['bool']['filter'][0])->toBe(['term' => ['is_active' => true]])
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
