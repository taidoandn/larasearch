<?php

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

it('shows the configured alias names in the health command output', function () {
    $client = fakeElasticsearchClient([
        'cluster_name' => 'larasearch',
        'status' => 'green',
    ], $http);

    app()->instance(Client::class, $client);

    $this->artisan('es:health')
        ->expectsOutputToContain('Elasticsearch is reachable.')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);
});

it('creates and deletes the configured index', function () {
    config()->set('elasticsearch.indexes.job_listings', 'job_listings_test');

    $client = fakeElasticsearchClientWithResponses([
        ['acknowledged' => true],
        ['acknowledged' => true],
    ], $http);

    app()->instance(Client::class, $client);

    $this->artisan('es:create-index')->assertExitCode(0);
    $this->artisan('es:delete-index')->assertExitCode(0);

    expect((string) $http->requests[0]->getUri())->toContain('/job_listings_test')
        ->and($http->requests[0]->getMethod())->toBe('PUT')
        ->and((string) $http->requests[1]->getUri())->toContain('/job_listings_test')
        ->and($http->requests[1]->getMethod())->toBe('DELETE');
});

it('switches the alias to the requested index', function () {
    $client = fakeElasticsearchClient(['acknowledged' => true], $http);

    app()->instance(Client::class, $client);

    $this->artisan('es:switch-alias job_listings_v2')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);

    expect(data_get($http->jsonBody(), 'actions.1.add.index'))->toBe('job_listings_v2')
        ->and(data_get($http->jsonBody(), 'actions.1.add.alias'))->toBe('job_listings_current');
});

it('reindexes job listings into a new index and switches the alias', function () {
    Queue::fake();

    JobListing::factory()->count(3)->create();

    $client = fakeElasticsearchClientWithResponses([
        ['acknowledged' => true],
        ['_shards' => ['successful' => 1]],
        ['acknowledged' => true],
    ], $http);

    $searchService = Mockery::mock(SearchServiceInterface::class);
    $searchService->shouldReceive('bulkIndexJobListings')
        ->once()
        ->with(Mockery::on(fn (Collection $jobListings): bool => $jobListings->count() === 3), 'job_listings_v2')
        ->andReturn(3);

    app()->instance(Client::class, $client);
    app()->instance(SearchServiceInterface::class, $searchService);

    $this->artisan('es:reindex job_listings_v2 --chunk=10')
        ->expectsOutputToContain('Indexed 3 job listings')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);

    expect(data_get($http->jsonBody(2), 'actions.1.add.index'))->toBe('job_listings_v2')
        ->and(data_get($http->jsonBody(2), 'actions.1.add.alias'))->toBe('job_listings_current');
});
