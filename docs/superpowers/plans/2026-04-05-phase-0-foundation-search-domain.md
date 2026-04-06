# Phase 0 Foundation & Search Domain Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 0 search foundation: local Elasticsearch/Kibana wiring, the core job-search schema and factories, and the Laravel services and commands needed to create, alias, and sync the first Elasticsearch index.

**Architecture:** MySQL remains the transactional source of truth and Elasticsearch is introduced as the user-facing read model behind an app-owned Laravel service boundary. The implementation is split into environment/config, relational domain, and Elasticsearch operations/sync so each slice can be tested independently and then composed. Search responses should be normalized by the application contract, while any database-backed search remains benchmark-only.

**Operational Notes:** Bulk indexing must support an explicit versioned target index for alias-based reindex flows. Search sync must also account for company-driven cascade deletes so Elasticsearch does not retain stale listing data. Denormalized taxonomy/admin edit reindexing is deferred until those write flows are in scope.

**Tech Stack:** Laravel 12, Laravel Sail, Pest 4, MySQL 8.4, Redis, Elasticsearch 8.x, Kibana, PHP 8.5

---

### Task 1: Environment and Elasticsearch Configuration

**Files:**
- Modify: `compose.yaml`
- Modify: `.env.example`
- Create: `config/elasticsearch.php`
- Test: `tests/Feature/Console/ElasticsearchHealthCommandTest.php`
- Test: `tests/Feature/Configuration/ElasticsearchConfigurationTest.php`

- [x] **Step 1: Write the failing configuration tests**

```php
it('defines the elasticsearch connection config', function () {
    expect(config('elasticsearch.host'))->toBeString();
    expect(config('elasticsearch.indexes.job_listings'))->toBe('job_listings_v1');
    expect(config('elasticsearch.aliases.job_listings'))->toBe('job_listings_current');
});
```

- [x] **Step 2: Run test to verify it fails**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Configuration/ElasticsearchConfigurationTest.php`
Expected: FAIL because `config/elasticsearch.php` does not exist.

- [x] **Step 3: Add Docker and configuration support**

```yaml
elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.17.3
    environment:
        discovery.type: single-node
        xpack.security.enabled: 'false'
```

```php
return [
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
    'indexes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX', 'job_listings_v1'),
    ],
    'aliases' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_ALIAS', 'job_listings_current'),
    ],
];
```

- [x] **Step 4: Run targeted tests**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Configuration/ElasticsearchConfigurationTest.php`
Expected: PASS.

### Task 2: Search Core Schema and Eloquent Domain

**Files:**
- Create: `database/migrations/*_create_companies_table.php`
- Create: `database/migrations/*_create_categories_table.php`
- Create: `database/migrations/*_create_skills_table.php`
- Create: `database/migrations/*_create_locations_table.php`
- Create: `database/migrations/*_create_job_listings_table.php`
- Create: `database/migrations/*_create_category_job_listing_table.php`
- Create: `database/migrations/*_create_job_listing_skill_table.php`
- Create: `app/Models/Company.php`
- Create: `app/Models/Category.php`
- Create: `app/Models/Skill.php`
- Create: `app/Models/Location.php`
- Create: `app/Models/JobListing.php`
- Create: `database/factories/CompanyFactory.php`
- Create: `database/factories/CategoryFactory.php`
- Create: `database/factories/SkillFactory.php`
- Create: `database/factories/LocationFactory.php`
- Create: `database/factories/JobListingFactory.php`
- Create: `database/seeders/JobMarketplaceSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/Database/JobSearchDomainTest.php`

- [x] **Step 1: Write the failing schema test**

```php
it('creates the phase 0 job search tables', function () {
    $this->assertDatabaseCount('companies', 0);
    $this->assertDatabaseCount('job_listings', 0);
});
```

- [x] **Step 2: Run test to verify it fails**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Database/JobSearchDomainTest.php`
Expected: FAIL because the tables do not exist.

- [x] **Step 3: Add migrations, models, relationships, factories, and seeder**

```php
class JobListing extends Model
{
    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

```php
JobListing::factory()
    ->count(10_000)
    ->create();
```

- [x] **Step 4: Run the domain tests**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Database/JobSearchDomainTest.php`
Expected: PASS.

### Task 3: Elasticsearch Client and Console Operations

**Files:**
- Create: `app/Services/ElasticsearchClient.php`
- Create: `app/Console/Commands/ElasticsearchHealthCommand.php`
- Create: `app/Console/Commands/ElasticsearchCreateIndexCommand.php`
- Create: `app/Console/Commands/ElasticsearchDeleteIndexCommand.php`
- Create: `app/Console/Commands/ElasticsearchSwitchAliasCommand.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Feature/Console/ElasticsearchCommandsTest.php`

- [x] **Step 1: Write the failing console tests**

```php
it('shows the configured alias names in the health command output', function () {
    $this->artisan('es:health')
        ->expectsOutputToContain('job_listings_current');
});
```

- [x] **Step 2: Run test to verify it fails**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchCommandsTest.php`
Expected: FAIL because the command is missing.

- [x] **Step 3: Implement the client wrapper and commands**

```php
final class ElasticsearchClient
{
    public function __construct(private readonly Client $client) {}

    public function health(): array
    {
        return $this->client->cluster()->health()->asArray();
    }
}
```

- [x] **Step 4: Run console tests**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchCommandsTest.php`
Expected: PASS with mocked client bindings.

### Task 4: Search Service Binding and Sync Scaffolding

**Files:**
- Create: `app/Contracts/SearchServiceInterface.php`
- Create: `app/Services/ElasticsearchSearchService.php`
- Create: `app/Jobs/SyncJobListingToElasticsearch.php`
- Create: `app/Observers/JobListingObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Models/JobListing.php`
- Create: `tests/Feature/Search/JobListingObserverTest.php`
- Create: `tests/Feature/Search/SyncJobListingToElasticsearchTest.php`
- Create: `tests/Feature/Search/JobListingElasticsearchE2ETest.php`

- [x] **Step 1: Write the failing sync tests**

```php
it('dispatches a sync job after a job listing is committed', function () {
    Queue::fake();

    JobListing::factory()->create();

    Queue::assertPushed(SyncJobListingToElasticsearch::class);
});
```

- [x] **Step 2: Run test to verify it fails**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Search/JobListingObserverTest.php tests/Feature/Search/SyncJobListingToElasticsearchTest.php`
Expected: FAIL because the observer and job do not exist.

- [x] **Step 3: Implement binding, observer, and queued sync**

```php
$this->app->bind(SearchServiceInterface::class, ElasticsearchSearchService::class);
JobListing::observe(JobListingObserver::class);
```

- [x] **Step 4: Run sync tests**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Search/JobListingObserverTest.php tests/Feature/Search/SyncJobListingToElasticsearchTest.php tests/Feature/Search/JobListingElasticsearchE2ETest.php`
Expected: PASS.

### Task 5: Bulk Indexing Command and Finishing Verification

**Files:**
- Create: `app/Console/Commands/ElasticsearchIndexJobListingsCommand.php`
- Test: `tests/Feature/Console/ElasticsearchIndexJobListingsCommandTest.php`

- [x] **Step 1: Write the failing bulk indexing test**

```php
it('indexes job listings in chunks', function () {
    JobListing::factory()->count(3)->create();

    $this->artisan('es:index-job-listings --chunk=2')
        ->expectsOutputToContain('Indexed 3 job listings');
});
```

- [x] **Step 2: Run test to verify it fails**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchIndexJobListingsCommandTest.php`
Expected: FAIL because the command is missing.

- [x] **Step 3: Implement chunked indexing**

```php
JobListing::query()
    ->with(['company', 'primaryLocation', 'categories', 'skills'])
    ->chunkById($chunkSize, fn (Collection $jobListings) => ...);
```

- [x] **Step 4: Run targeted verification and formatting**

Run: `vendor/bin/sail artisan test --compact tests/Feature/Configuration/ElasticsearchConfigurationTest.php tests/Feature/Database/JobSearchDomainTest.php tests/Feature/Console/ElasticsearchCommandsTest.php tests/Feature/Console/ElasticsearchIndexJobListingsCommandTest.php tests/Feature/Search/JobListingObserverTest.php tests/Feature/Search/SyncJobListingToElasticsearchTest.php`
Expected: PASS.

Run: `vendor/bin/sail bin pint --dirty --format agent`
Expected: formatted PHP files with exit code 0.
