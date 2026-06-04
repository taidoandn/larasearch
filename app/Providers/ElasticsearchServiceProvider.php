<?php

namespace App\Providers;

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
    }
}
