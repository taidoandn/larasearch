# Technical Reference - Search Contract & Elasticsearch Integration

> Canonical technical reference for the Larasearch search contract, Elasticsearch read model, and operational workflow.

## Canonical Rules

- MySQL is the transactional source of truth.
- Elasticsearch is the user-facing job search read model.
- Application code reads and writes through the `job_listings_current` alias, not a concrete versioned index.
- Controllers and UI consume app-owned normalized output. Raw Elasticsearch `hits`, `_source`, `_score`, and highlight arrays must not leak into page props.
- Elasticsearch helper fields such as `skills_text`, `skill_slugs`, and autocomplete subfields are internal implementation details.

## Current Structure

```text
app/
├── Search/
│   ├── Client/ElasticsearchClient.php
│   ├── Builders/BaseQueryBuilder.php
│   ├── Builders/JobListingQueryBuilder.php
│   ├── Filters/JobListingFilters.php
│   ├── Indexers/BaseIndexer.php
│   ├── Indexers/JobListingIndexer.php
│   ├── Searchers/JobListingSearcher.php
│   └── Utils/
│       ├── SearchNormalizer.php
│       └── SearchResponseFormatter.php
├── Services/
│   └── JobListingSearchService.php
├── Jobs/SyncJobListingToElasticsearch.php
├── Observers/JobListingObserver.php
└── Console/Commands/*JobListing*Command.php
```

| Component | Responsibility |
| --- | --- |
| `ElasticsearchClient` | Builds the official Elasticsearch PHP client from config/env only |
| `BaseQueryBuilder` | Generic Elasticsearch DSL helpers |
| `JobListingQueryBuilder` | Job-listing search, facets, sorting, visibility filters, and multi-field suggest request bodies |
| `JobListingFilters` | Job-listing filter defaults and domain normalization |
| `SearchNormalizer` | Generic input helpers: keyword, lists, slugs, enums, numeric values, pagination, default compaction |
| `JobListingSearcher` | Executes search/suggest API calls through the alias and returns normalized app payloads |
| `SearchResponseFormatter` | Generic ES response helpers only; no job-listing-specific formatting |
| `JobListingIndexer` | Create/delete/refresh/switch alias, index one, bulk index many, delete one, full reindex |
| `JobListing::toSearchDocument()` | Maps the Eloquent model and loaded relations into the Elasticsearch document |
| `SyncJobListingToElasticsearch` + observer | Queue after-commit incremental sync and delete jobs |

## Request Flow

### Search page

1. Authenticated user requests `GET /jobs`.
2. `SearchRequest` validates query params.
3. `JobsController` delegates to `JobListingSearchService`.
4. `JobListingFilters::normalize()` sanitizes and normalizes the app filter shape.
5. `JobListingSearcher` builds DSL through `JobListingQueryBuilder`.
6. Elasticsearch is queried through `config('elasticsearch.aliases.job_listings')`.
7. `JobListingSearcher` maps hits, facets, highlights, and pagination into the page contract.
8. Inertia renders `resources/js/pages/jobs/index.tsx`.

### Suggestions

1. Authenticated user requests `GET /jobs/suggest?q=lar`.
2. `JobSuggestController` validates the keyword.
3. `JobListingSearchService::suggest()` delegates to `JobListingSearcher`.
4. `JobListingQueryBuilder::suggestBody()` queries autocomplete subfields across title, skills, and company with visibility filters.
5. `JobListingSearcher` returns at most 5 unique `{ label, type }` suggestions.

## Search Request Contract

Supported params:

```text
q=laravel backend
skills[]=php
skills[]=mysql
location[]=da-nang
category[]=backend
job_type[]=full-time
work_model[]=hybrid
experience_level[]=senior
salary_min=1000
salary_max=3000
sort=best_match
page=1
per_page=20
```

Rules:

- Empty `q` means filtered browse, not an error.
- Slug filters are normalized with `SearchNormalizer::slugList()`.
- Enum filters are normalized through the backing enums.
- `salary_min` and `salary_max` are non-negative integers or `null`.
- Supported sorts are `best_match`, `newest`, `salary_desc`, and `salary_asc`.
- `page` defaults to `1`; `per_page` defaults to `20` and is capped at `50`.
- Default hard filters are `is_active = true`, `published_at <= now`, and `expires_at` missing or in the future.

## Search Response Contract

The search response uses Laravel paginator-compatible keys plus search metadata.

```json
{
    "data": [
        {
            "id": 1,
            "slug": "senior-laravel-backend-engineer",
            "title": "Senior Laravel Backend Engineer",
            "description": "Build scalable APIs with Laravel.",
            "application_url": "https://jobs.example.test/apply/senior-laravel-backend-engineer",
            "company": {
                "name": "Acme Tech",
                "slug": "acme-tech",
                "logo_url": "https://cdn.example.test/acme-logo.png",
                "website": "https://acme.example.test"
            },
            "primary_location": "Da Nang",
            "locations": ["Da Nang"],
            "skills": ["Laravel", "PHP"],
            "salary": {
                "min": 1500,
                "max": 2500,
                "currency": "USD",
                "is_visible": true
            },
            "job_type": "full-time",
            "job_type_label": "Full-time",
            "work_model": "hybrid",
            "work_model_label": "Hybrid",
            "experience_level": "senior",
            "experience_level_label": "Senior",
            "published_at": "2026-04-01T09:00:00Z",
            "highlight": {
                "title": null,
                "description": null
            }
        }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 142,
    "from": 1,
    "to": 20,
    "last_page": 8,
    "facets": {
        "locations": [],
        "categories": [],
        "skills": [],
        "job_types": [],
        "work_models": [],
        "experience_levels": []
    },
    "sort": "best_match"
}
```

Response notes:

- `data` is always present, even when empty.
- `from` and `to` may be `null` when there are no results.
- Facet items use `{ value, label, count }`.
- `highlight` is normalized app output, not the raw Elasticsearch highlight payload.
- Salary filters are overlap-based so one-sided salary documents remain searchable.

## Suggest Contract

```text
GET /jobs/suggest?q=lar
```

```json
{
    "items": [
        { "label": "Senior Laravel Backend Engineer", "type": "job_title" },
        { "label": "Laravel", "type": "skill" }
    ]
}
```

Suggest notes:

- `items` is capped at 5 suggestions.
- Supported `type` values are `job_title`, `skill`, and `company`.
- Matching uses a filtered multi-field autocomplete query across job title, skills, and company name.
- Labels are deduplicated by `type:label`.

## Elasticsearch Document

`JobListing::toSearchDocument()` owns the document shape. The mapping/settings live in `config/elasticsearch_indices.php`.

```json
{
    "id": 123,
    "slug": "senior-backend-engineer-acme-tech",
    "title": "Senior Backend Engineer",
    "description": "Build scalable APIs using Laravel and MySQL.",
    "short_description": "Build scalable APIs with Laravel.",
    "application_url": "https://jobs.example.test/apply/senior-backend-engineer",
    "company_name": "Acme Tech",
    "company_slug": "acme-tech",
    "company_logo_url": "https://cdn.example.test/acme-logo.png",
    "company_website": "https://acme.example.test",
    "location_slugs": ["da-nang"],
    "location_labels": ["Da Nang"],
    "category_slugs": ["backend"],
    "category_names": ["Backend"],
    "skill_slugs": ["laravel", "php", "mysql"],
    "skills": ["Laravel", "PHP", "MySQL"],
    "skills_text": "Laravel PHP MySQL",
    "job_type": "full-time",
    "work_model": "hybrid",
    "experience_level": "senior",
    "salary_min": 1500,
    "salary_max": 2500,
    "salary_currency": "USD",
    "salary_is_visible": true,
    "is_featured": false,
    "is_active": true,
    "published_at": "2026-04-01T09:00:00Z",
    "expires_at": null
}
```

Field groups:

- Text search: `title`, `description`, `short_description`, `company_name`, `skills_text`.
- Filters/facets: `location_slugs`, `category_slugs`, `skill_slugs`, `job_type`, `work_model`, `experience_level`.
- Sort/range: `published_at`, `salary_min`, `salary_max`, `id`.
- Autocomplete: `title.autocomplete`, `skills_text.autocomplete`, and `company_name.autocomplete`.
- Visibility: `is_active`, `published_at`, `expires_at`.

## Config

`config/elasticsearch.php` owns connection, auth, retry, SSL, queue, alias, index, and prefix settings.

```php
return [
    'hosts' => ['http://elasticsearch:9200'],
    'indexes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX', 'job_listings_v1'),
    ],
    'index_prefixes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX_PREFIX', 'job_listings_'),
    ],
    'aliases' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_ALIAS', 'job_listings_current'),
    ],
    'indices' => require config_path('elasticsearch_indices.php'),
];
```

`config/elasticsearch_indices.php` currently contains the static `job_listings` settings and mappings. It uses lowercase/asciifolding analyzers plus edge-ngram autocomplete subfields for suggestions. ICU is not required by default.

## Index Alias & Reindexing

```text
Before:
app -> job_listings_current -> job_listings_20260401_120000

Reindex:
create job_listings_20260605_120000
bulk import MySQL job data into job_listings_20260605_120000
refresh job_listings_20260605_120000
switch alias job_listings_current -> job_listings_20260605_120000

After:
app -> job_listings_current -> job_listings_20260605_120000
```

`JobListingIndexer::reindex()` performs this flow for job listings. `BaseIndexer::switchAlias()` updates the alias atomically through Elasticsearch `_aliases`.

## Operational Workflow

Run all PHP/Artisan commands through Sail.

### Start local services

```bash
vendor/bin/sail up -d
vendor/bin/sail artisan es:health --no-interaction
```

`es:health` checks cluster reachability and prints the configured job listings alias.

### Data backfill only

Use this when mappings did not change and the alias target only needs current MySQL data.

```bash
vendor/bin/sail artisan es:job-listings:index --no-interaction
```

Use an explicit target index when validating a non-live index:

```bash
vendor/bin/sail artisan es:job-listings:index --index=job_listings_YYYYMMDD_HHMMSS --no-interaction
```

### Zero-downtime rebuild

Use this after mapping, analyzer, document shape, sort, or filter field changes.

```bash
vendor/bin/sail artisan es:job-listings:reindex job_listings_YYYYMMDD_HHMMSS --chunk=250 --no-interaction
```

This creates the target index, bulk indexes all job listings in chunks, refreshes the index, and switches the alias.

### Manual create -> index -> swap

Use this when each step needs inspection before alias switch.

```bash
vendor/bin/sail artisan es:job-listings:create-index job_listings_YYYYMMDD_HHMMSS --no-interaction
vendor/bin/sail artisan es:job-listings:index --index=job_listings_YYYYMMDD_HHMMSS --no-interaction
vendor/bin/sail artisan es:job-listings:switch-alias job_listings_YYYYMMDD_HHMMSS --no-interaction
```

### Cleanup old inactive indices

```bash
vendor/bin/sail artisan es:job-listings:cleanup-old-indices --keep=2 --no-interaction
```

The cleanup command deletes inactive indices matching the configured `job_listings` prefix and never deletes the active alias target.

### Queue worker

Incremental sync is queued after database commit. Run a worker when testing observer-driven sync outside the test suite:

```bash
vendor/bin/sail artisan queue:work database --queue=default --tries=3 --timeout=60
```

### Command summary

| Command | Use it when |
| --- | --- |
| `vendor/bin/sail artisan es:health --no-interaction` | Check cluster reachability and configured alias |
| `vendor/bin/sail artisan es:job-listings:index --no-interaction` | Backfill the active job listings alias target |
| `vendor/bin/sail artisan es:job-listings:reindex <index> --no-interaction` | Rebuild into a versioned index and switch alias |
| `vendor/bin/sail artisan es:job-listings:create-index <index> --no-interaction` | Create a target job listings index manually |
| `vendor/bin/sail artisan es:job-listings:delete-index <index> --no-interaction` | Delete a job listings index manually |
| `vendor/bin/sail artisan es:job-listings:switch-alias <index> --no-interaction` | Point the alias at a validated target index |
| `vendor/bin/sail artisan es:job-listings:cleanup-old-indices --keep=2 --no-interaction` | Remove inactive old versioned indices |

## Test Workflow

Fast local checks:

```bash
vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchCommandsTest.php tests/Feature/Console/ElasticsearchIndexJobListingsCommandTest.php tests/Feature/Console/ElasticsearchCleanupOldIndicesCommandTest.php
vendor/bin/sail artisan test --compact tests/Unit/ElasticsearchSearchServiceTest.php tests/Unit/JobSuggestServiceTest.php tests/Unit/Search/JobListingQueryBuilderTest.php tests/Unit/SearchNormalizerTest.php tests/Unit/Search/SearchResponseFormatterTest.php
vendor/bin/sail artisan test --compact tests/Feature/Search/SyncJobListingToElasticsearchTest.php tests/Feature/Search/JobListingObserverTest.php
```

Live Elasticsearch E2E:

```bash
docker exec -e ENABLE_LIVE_ES_TESTS=true larasearch-laravel.test-1 php artisan test --compact tests/Feature/Search/JobListingElasticsearchE2ETest.php
```

The live test is skipped unless `ENABLE_LIVE_ES_TESTS=true` is present in the PHP process.

## Consistency Model

- Writes go to MySQL first.
- Sync jobs run after database commit.
- Search reads come from Elasticsearch.
- Rebuilds use bulk DB -> ES indexing plus alias swap.
- Temporary drift between MySQL and Elasticsearch is expected operationally and should be repaired by queued sync or bulk backfill.
