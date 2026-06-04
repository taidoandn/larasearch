<?php

namespace App\Providers;

use App\Search\Client\ElasticsearchClient;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            return (new ElasticsearchClient)->build();
        });
    }
}
