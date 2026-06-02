<?php

use App\Contracts\SearchServiceInterface;
use App\Services\JobListingSearchService;
use Elastic\Elasticsearch\Client;

test('the official elasticsearch php client is bound in the container', function () {
    expect(app(Client::class))->toBeInstanceOf(Client::class);
});

test('the job listing search service is bound to the search contract', function () {
    expect(app(SearchServiceInterface::class))->toBeInstanceOf(JobListingSearchService::class);
});
