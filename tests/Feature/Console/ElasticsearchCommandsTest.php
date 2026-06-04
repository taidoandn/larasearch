<?php

use App\Models\JobListing;
use App\Search\Indexers\JobListingIndexer;
use Elastic\Elasticsearch\Client;
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

    $this->artisan('es:job-listings:create-index')->assertExitCode(0);
    $this->artisan('es:job-listings:delete-index')->assertExitCode(0);

    expect((string) $http->requests[0]->getUri())->toContain('/job_listings_test')
        ->and($http->requests[0]->getMethod())->toBe('PUT')
        ->and((string) $http->requests[1]->getUri())->toContain('/job_listings_test')
        ->and($http->requests[1]->getMethod())->toBe('DELETE');
});

it('switches the alias to the requested index', function () {
    $client = fakeElasticsearchClient(['acknowledged' => true], $http);

    app()->instance(Client::class, $client);

    $this->artisan('es:job-listings:switch-alias job_listings_v2')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);

    expect(data_get($http->jsonBody(), 'actions.1.add.index'))->toBe('job_listings_v2')
        ->and(data_get($http->jsonBody(), 'actions.1.add.alias'))->toBe('job_listings_current');
});

it('reindexes job listings into a new index and switches the alias', function () {
    Queue::fake();

    JobListing::factory()->count(3)->create();

    $client = fakeElasticsearchClient(['acknowledged' => true]);

    $searchService = Mockery::mock(JobListingIndexer::class);
    $searchService->shouldReceive('reindex')
        ->once()
        ->with('job_listings_v2', 10, 'job_listings_current')
        ->andReturn(3);

    app()->instance(Client::class, $client);
    app()->instance(JobListingIndexer::class, $searchService);

    $this->artisan('es:job-listings:reindex job_listings_v2 --chunk=10')
        ->expectsOutputToContain('Indexed 3 job listings')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);
});
