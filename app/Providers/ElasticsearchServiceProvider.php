<?php

namespace App\Providers;

use App\Contracts\SearchServiceInterface;
use App\Services\JobListingSearchService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            return ClientBuilder::create()
                ->setHosts([(string) config('elasticsearch.host')])
                ->setHttpClientOptions([
                    'timeout' => (int) config('elasticsearch.timeout', 5),
                ])
                ->build();
        });

        $this->app->bind(SearchServiceInterface::class, JobListingSearchService::class);
    }
}
