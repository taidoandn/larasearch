<?php

namespace App\Providers;

use App\Contracts\SearchServiceInterface;
use App\Models\Company;
use App\Models\JobListing;
use App\Observers\CompanyObserver;
use App\Observers\JobListingObserver;
use App\Services\DatabaseSearchService;
use App\Services\ElasticsearchClient;
use App\Services\ElasticsearchSearchService;
use Carbon\CarbonImmutable;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
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

        $this->app->singleton(ElasticsearchClient::class, function ($app): ElasticsearchClient {
            return new ElasticsearchClient(
                client: $app->make(Client::class),
            );
        });

        $this->app->bind(SearchServiceInterface::class, function ($app): SearchServiceInterface {
            if (! config('elasticsearch.enabled', true)) {
                return new DatabaseSearchService;
            }

            return new ElasticsearchSearchService(
                client: $app->make(ElasticsearchClient::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Company::observe(CompanyObserver::class);
        JobListing::observe(JobListingObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
