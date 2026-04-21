<?php

use App\Contracts\SearchServiceInterface;
use App\Jobs\SyncJobListingToElasticsearch;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Location;
use App\Models\Skill;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

function skipUnlessLiveElasticsearchEnabled(Client $client, TestCase $testCase): void
{
    if (! filter_var(env('ENABLE_LIVE_ES_TESTS', false), FILTER_VALIDATE_BOOL)) {
        $testCase->markTestSkipped('Live Elasticsearch tests are disabled. Set ENABLE_LIVE_ES_TESTS=true to run them.');
    }

    try {
        $client->cluster()->health();
    } catch (Throwable $exception) {
        $testCase->markTestSkipped('Elasticsearch is not reachable: '.$exception->getMessage());
    }
}

/**
 * @return array{suffix: string, index: string, alias: string}
 */
function createLiveTestIndex(Client $client): array
{
    $suffix = Str::lower((string) Str::uuid());
    $index = "job_listings_test_{$suffix}";
    $alias = "job_listings_test_alias_{$suffix}";

    config()->set('elasticsearch.enabled', true);
    config()->set('elasticsearch.indexes.job_listings', $index);
    config()->set('elasticsearch.aliases.job_listings', $alias);

    $client->indices()->create([
        'index' => $index,
        'body' => config('elasticsearch.mapping'),
    ]);

    $client->indices()->updateAliases([
        'body' => [
            'actions' => [
                [
                    'add' => [
                        'index' => $index,
                        'alias' => $alias,
                    ],
                ],
            ],
        ],
    ]);

    return [
        'suffix' => $suffix,
        'index' => $index,
        'alias' => $alias,
    ];
}

/**
 * @return array{company: Company, location: Location, categories: Collection<int, Category>, skills: Collection<int, Skill>}
 */
function createLiveJobListingRelations(string $suffix): array
{
    return [
        'company' => Company::factory()->create([
            'name' => "Search Test Co {$suffix}",
            'slug' => "search-test-co-{$suffix}",
        ]),
        'location' => Location::factory()->create([
            'display_name' => "Bangkok Test {$suffix}",
            'city_name' => "Bangkok Test {$suffix}",
        ]),
        'categories' => Category::factory()->count(2)->sequence(
            ['name' => "Search Infra {$suffix}", 'slug' => "search-infra-{$suffix}"],
            ['name' => "Platform {$suffix}", 'slug' => "platform-{$suffix}"],
        )->create(),
        'skills' => Skill::factory()->count(2)->sequence(
            ['name' => "Elasticsearch {$suffix}", 'slug' => "elasticsearch-{$suffix}"],
            ['name' => "Laravel {$suffix}", 'slug' => "laravel-{$suffix}"],
        )->create(),
    ];
}

function cleanupLiveTestIndex(Client $client, string $index, string $alias, int $jobListingId): void
{
    try {
        $client->delete([
            'index' => $alias,
            'id' => (string) $jobListingId,
        ]);
    } catch (ClientResponseException) {
    }

    try {
        $client->indices()->deleteAlias([
            'index' => $index,
            'name' => $alias,
        ]);
    } catch (ClientResponseException) {
    }

    try {
        $client->indices()->delete([
            'index' => $index,
        ]);
    } catch (ClientResponseException) {
    }
}

it('creates a job listing, dispatches sync, and indexes the document in elasticsearch', function () {
    $client = app(Client::class);

    skipUnlessLiveElasticsearchEnabled($client, $this);

    ['suffix' => $suffix, 'index' => $index, 'alias' => $alias] = createLiveTestIndex($client);
    ['company' => $company, 'location' => $location, 'categories' => $categories, 'skills' => $skills] = createLiveJobListingRelations($suffix);

    $capturedJob = null;

    Queue::fake();

    $jobListing = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => "staff-search-platform-engineer-{$suffix}",
            'title' => "Staff Search Platform Engineer {$suffix}",
        ]);

    $jobListing->categories()->sync($categories->pluck('id')->all());
    $jobListing->skills()->sync([
        $skills[0]->id => ['is_primary' => true, 'weight' => 3],
        $skills[1]->id => ['is_primary' => false, 'weight' => 2],
    ]);

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing, &$capturedJob): bool {
        $capturedJob = $job;

        return $job->jobListingId === $jobListing->id
            && $job->delete === false
            && $job->afterCommit === true;
    });

    expect($capturedJob)->toBeInstanceOf(SyncJobListingToElasticsearch::class);

    $capturedJob->handle(app(SearchServiceInterface::class));

    $client->indices()->refresh([
        'index' => $index,
    ]);

    $document = $client->get([
        'index' => $alias,
        'id' => (string) $jobListing->id,
    ])->asArray();

    expect($document)->not->toBeNull()
        ->and(data_get($document, '_source.id'))->toBe($jobListing->id)
        ->and(data_get($document, '_source.slug'))->toBe("staff-search-platform-engineer-{$suffix}")
        ->and(data_get($document, '_source.title'))->toBe("Staff Search Platform Engineer {$suffix}")
        ->and(data_get($document, '_source.company_name'))->toBe("Search Test Co {$suffix}")
        ->and(data_get($document, '_source.company_slug'))->toBe("search-test-co-{$suffix}")
        ->and(data_get($document, '_source.location_slugs'))->toBe([Str::slug("Bangkok Test {$suffix}")])
        ->and(data_get($document, '_source.location_labels'))->toBe(["Bangkok Test {$suffix}"])
        ->and(data_get($document, '_source.category_names'))->toBe(["Search Infra {$suffix}", "Platform {$suffix}"])
        ->and(collect(data_get($document, '_source.skills'))->sort()->values()->all())->toBe([
            "Elasticsearch {$suffix}",
            "Laravel {$suffix}",
        ]);

    cleanupLiveTestIndex($client, $index, $alias, $jobListing->id);
});

it('deletes a job listing, dispatches delete sync, and removes the document from elasticsearch', function () {
    if (! filter_var(env('ENABLE_LIVE_ES_TESTS', false), FILTER_VALIDATE_BOOL)) {
        $this->markTestSkipped('Live Elasticsearch tests are disabled. Set ENABLE_LIVE_ES_TESTS=true to run them.');
    }

    $client = app(Client::class);

    skipUnlessLiveElasticsearchEnabled($client, $this);

    ['suffix' => $suffix, 'index' => $index, 'alias' => $alias] = createLiveTestIndex($client);
    ['company' => $company, 'location' => $location, 'categories' => $categories, 'skills' => $skills] = createLiveJobListingRelations($suffix);

    $createJob = null;
    Queue::fake();

    $jobListing = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => "staff-search-delete-engineer-{$suffix}",
            'title' => "Staff Search Delete Engineer {$suffix}",
        ]);

    $jobListing->categories()->sync($categories->pluck('id')->all());
    $jobListing->skills()->sync([
        $skills[0]->id => ['is_primary' => true, 'weight' => 3],
        $skills[1]->id => ['is_primary' => false, 'weight' => 2],
    ]);

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing, &$createJob): bool {
        $createJob = $job;

        return $job->jobListingId === $jobListing->id
            && $job->delete === false
            && $job->afterCommit === true;
    });

    $createJob->handle(app(SearchServiceInterface::class));

    $client->indices()->refresh([
        'index' => $index,
    ]);

    expect($client->get([
        'index' => $alias,
        'id' => (string) $jobListing->id,
    ])->asArray())->not->toBeNull();

    $deleteJob = null;
    Queue::fake();

    $jobListing->delete();

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing, &$deleteJob): bool {
        $deleteJob = $job;

        return $job->jobListingId === $jobListing->id
            && $job->delete === true
            && $job->afterCommit === true;
    });

    $deleteJob->handle(app(SearchServiceInterface::class));

    $client->indices()->refresh([
        'index' => $index,
    ]);

    $document = rescue(function () use ($client, $alias, $jobListing): ?array {
        return $client->get([
            'index' => $alias,
            'id' => (string) $jobListing->id,
        ])->asArray();
    }, rescue: function () {
        return null;
    }, report: false);

    expect($document)->toBeNull();

    cleanupLiveTestIndex($client, $index, $alias, $jobListing->id);
});

it('searches indexed job listings with normalized filters and facets', function () {
    if (! filter_var(env('ENABLE_LIVE_ES_TESTS', false), FILTER_VALIDATE_BOOL)) {
        $this->markTestSkipped('Live Elasticsearch tests are disabled. Set ENABLE_LIVE_ES_TESTS=true to run them.');
    }

    $client = app(Client::class);

    skipUnlessLiveElasticsearchEnabled($client, $this);

    ['suffix' => $suffix, 'index' => $index, 'alias' => $alias] = createLiveTestIndex($client);
    ['company' => $company, 'location' => $location, 'categories' => $categories, 'skills' => $skills] = createLiveJobListingRelations($suffix);

    Queue::fake();

    $matchingJob = JobListing::factory()
        ->for($company)
        ->for($location, 'primaryLocation')
        ->create([
            'slug' => "senior-laravel-search-engineer-{$suffix}",
            'title' => "Senior Laravel Search Engineer {$suffix}",
        ]);

    $matchingJob->categories()->sync($categories->pluck('id')->all());
    $matchingJob->skills()->sync([
        $skills[0]->id => ['is_primary' => true, 'weight' => 3],
        $skills[1]->id => ['is_primary' => false, 'weight' => 2],
    ]);

    $queuedJob = null;

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use (&$queuedJob): bool {
        $queuedJob = $job;

        return $job->delete === false;
    });

    $queuedJob->handle(app(SearchServiceInterface::class));

    $client->indices()->refresh([
        'index' => $index,
    ]);

    $results = app(SearchServiceInterface::class)->search([
        'q' => "Laravel {$suffix}",
        'location' => Str::slug($location->city_name),
        'category' => $categories[0]->slug,
        'skills' => $skills[0]->slug,
        'sort' => 'best_match',
        'page' => 1,
        'per_page' => 10,
    ]);

    expect($results['items'])->toHaveCount(1)
        ->and($results['items'][0]['id'])->toBe($matchingJob->id)
        ->and($results['items'][0]['slug'])->toBe("senior-laravel-search-engineer-{$suffix}")
        ->and($results['facets']['locations'][0]['value'])->toBe(Str::slug($location->city_name))
        ->and($results['facets']['locations'][0]['label'])->toBe($location->display_name)
        ->and($results['pagination']['total'])->toBe(1);

    cleanupLiveTestIndex($client, $index, $alias, $matchingJob->id);
});
