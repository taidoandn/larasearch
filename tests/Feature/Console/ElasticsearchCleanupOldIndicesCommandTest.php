<?php

use Elastic\Elasticsearch\Client;

it('cleans up old inactive job listing indices and keeps the active alias target', function () {
    $client = fakeElasticsearchClientWithResponses([
        [
            'job_listings_20260401_120000' => ['aliases' => []],
            'job_listings_20260402_120000' => ['aliases' => []],
            'job_listings_20260403_120000' => ['aliases' => ['job_listings_current' => []]],
            'job_listings_20260404_120000' => ['aliases' => []],
        ],
        ['acknowledged' => true],
    ], $http);

    app()->instance(Client::class, $client);

    $this->artisan('es:job-listings:cleanup-old-indices --keep=1')
        ->expectsOutputToContain('Deleted old index [job_listings_20260401_120000].')
        ->assertExitCode(0);

    expect(rawurldecode((string) $http->requests[0]->getUri()))->toContain('/job_listings_*')
        ->and($http->requests[1]->getMethod())->toBe('DELETE')
        ->and((string) $http->requests[1]->getUri())->toContain('/job_listings_20260401_120000');
});
