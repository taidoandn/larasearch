# Technical Reference — Search Contract & Elasticsearch Integration

> Canonical technical reference for the Larasearch search contract, Elasticsearch read model, and search service responsibilities.

## Canonical Rules

- MySQL is the transactional source of truth.
- Elasticsearch is the user-facing search read model.
- Controllers and UI consume an app-owned normalized contract.
- Elasticsearch helper fields such as `skills_text` are internal implementation details and must not leak into controller or UI contracts.

## Current Implementation Notes

- The authenticated `/jobs` page is served by `JobsController` and rendered through the Inertia page at `resources/js/pages/jobs/index.tsx`.
- The authenticated `/jobs/{job:slug}` detail page is served by `JobShowController` and rendered through `resources/js/pages/jobs/show.tsx`.
- Query validation is handled by `SearchRequest`.
- `ElasticsearchSearchService` normalizes backend responses into the canonical search contract before the page consumes them.
- The suggest endpoint is served by `JobSuggestController` and backed by `JobSuggestService`.
- The job detail page uses a separate documented payload from `JobShowController`.

## Search Request Contract

### Supported params

```text
q=laravel backend
skills[]=php
skills[]=mysql
location=da-nang
category=backend
job_type=full-time
work_model=hybrid
experience_level=senior
salary_min=1000
salary_max=3000
sort=best_match
page=1
per_page=20
```

### Rules

- Empty `q` means filtered browse, not an error.
- `category` and `skills[]` are normalized to canonical slug values before Elasticsearch filtering and before search context is round-tripped back into `/jobs`.
- Default hard filters are:
  - `is_active = true`
  - `published_at <= now`
  - `expires_at` is null or in the future
- Supported sorts for Search MVP:
  - `best_match`
  - `newest`
  - `salary_desc`
  - `salary_asc`
- Pagination is page-based for the MVP.
- `per_page` should be capped by the application to a reasonable maximum such as `50`.
- Sorting should include a deterministic secondary sort such as `id desc` when primary values tie.

## Search Response Contract

This is the single public result shape consumed by controllers and UI.

```json
{
    "items": [
        {
            "id": 1,
            "slug": "senior-laravel-backend-engineer-acme-tech",
            "title": "Senior Laravel Backend Engineer",
            "application_url": "https://jobs.example.test/apply/senior-laravel-backend-engineer",
            "company": {
                "name": "Acme Tech",
                "slug": "acme-tech",
                "website": "https://acme.example.test"
            },
            "primary_location": "Da Nang",
            "locations": ["Da Nang"],
            "skills": ["PHP", "Laravel", "MySQL"],
            "salary": {
                "min": 1500,
                "max": 2500,
                "currency": "USD",
                "is_visible": true
            },
            "job_type": "full-time",
            "work_model": "hybrid",
            "experience_level": "senior",
            "published_at": "2026-04-01T09:00:00Z",
            "highlight": {
                "title": null,
                "description": null
            }
        }
    ],
    "pagination": {
        "page": 1,
        "per_page": 20,
        "total": 142,
        "from": 1,
        "to": 20,
        "total_pages": 8,
        "has_more": true
    },
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

### Response notes

- `items` is always present, even when empty.
- `pagination.from` and `pagination.to` describe the visible row range for the current page and can be used for UI copy such as `Showing 21 to 21 of 21 results`.
- Facet items may include a human-facing `label` alongside the canonical `value`.
- `facets` may be empty objects or empty arrays when not requested yet, but the top-level key should remain stable.
- `highlight` is normalized application output, not raw Elasticsearch highlight payloads.
- Backends must return this contract even when their internal query engines differ.
- Salary range filters are overlap-based and should keep one-sided salary documents searchable when only `salary_min` or `salary_max` is present in the indexed document.

## Suggest Contract

The suggest endpoint is served via a Laravel controller action for the Inertia search experience.

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

### Suggest notes

- `items` is capped at 5 suggestions.
- The backend may fetch more than 5 Elasticsearch hits internally before deduplicating so duplicate-heavy hit sets can still fill the 5-item response.
- Suggest labels should be relevant to the typed keyword rather than every metadata field on a matching job hit.
- Suggest matching allows safe typo tolerance for near matches such as `laravl` -> `Laravel`.
- Suggestion `type` currently supports `job_title`, `skill`, and `company`.

## Job Detail Contract

The dedicated `jobs.show` page uses a separate payload shape from the search results list.

```json
{
    "job": {
        "id": 1,
        "slug": "senior-laravel-backend-engineer-acme-tech",
        "title": "Senior Laravel Backend Engineer",
        "application_url": "https://jobs.example.test/apply/senior-laravel-backend-engineer",
        "company": {
            "name": "Acme Tech",
            "slug": "acme-tech",
            "summary": "Remote-first product engineering team.",
            "meta": "SaaS • 51-200 • VN",
            "website": "https://acme.example.test"
        },
        "primary_location": "Da Nang",
        "locations": ["Da Nang"],
        "job_type": "full-time",
        "work_model": "hybrid",
        "skills": ["Laravel", "PHP"],
        "summary_metrics": [],
        "requirements": [],
        "published_at": "2026-04-01T09:00:00Z"
    },
    "relatedJobs": [],
    "searchContext": {
        "index_query": {}
    }
}
```

## Naming Map

| Relational source | Elasticsearch field | Public contract |
| --- | --- | --- |
| `job_listings.id` | `id` | `id` |
| `job_listings.slug` | `slug` | `slug` |
| `job_listings.title` | `title` | `title` |
| `job_listings.description` | `description` | `highlight.description` source |
| `job_listings.job_type` | `job_type` | `job_type` |
| `job_listings.work_model` | `work_model` | `work_model` |
| `job_listings.experience_level` | `experience_level` | `experience_level` |
| `job_listings.salary_min` | `salary_min` | `salary.min` |
| `job_listings.salary_max` | `salary_max` | `salary.max` |
| `job_listings.salary_currency` | `salary_currency` | `salary.currency` |
| `job_listings.salary_is_visible` | `salary_is_visible` | `salary.is_visible` |
| `job_listings.published_at` | `published_at` | `published_at` |
| `job_listings.expires_at` | `expires_at` | internal filter field |
| `companies.name` | `company_name` | `company.name` |
| `companies.slug` | `company_slug` | `company.slug` |
| `locations.city_name` | `location_slugs` | filter/facet `value` |
| `locations.display_name` | `location_labels` | `locations`, `primary_location`, facet `label` |
| `categories.slug` | `category_slugs` | facet/filter `value` |
| `categories.name` | `category_names` | facet `label` |
| `skills.slug` | `skill_slugs` | facet/filter `value` |
| `skills.name` | `skills` | `skills`, facet `label` |
| n/a | `skills_text` | internal full-text helper only |

## Elasticsearch Job Document

This is the canonical read model for the `job_listings_current` alias.

```json
{
    "id": 123,
    "slug": "senior-backend-engineer-acme-tech",
    "title": "Senior Backend Engineer",
    "description": "Build scalable APIs using Laravel and MySQL...",
    "short_description": "Build scalable APIs with Laravel.",
    "company_name": "Acme Tech",
    "company_slug": "acme-tech",
    "location_slugs": ["da-nang"],
    "location_labels": ["Da Nang"],
    "category_slugs": ["backend"],
    "category_names": ["Backend"],
    "skill_slugs": ["laravel", "php", "mysql", "redis"],
    "skills": ["Laravel", "PHP", "MySQL", "Redis"],
    "skills_text": "Laravel PHP MySQL Redis",
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

## Elasticsearch Mapping (job_listings_v1)

```json
{
    "settings": {
        "number_of_shards": 1,
        "number_of_replicas": 0,
        "analysis": {
            "filter": {
                "autocomplete_filter": {
                    "type": "edge_ngram",
                    "min_gram": 2,
                    "max_gram": 20
                }
            },
            "analyzer": {
                "autocomplete_analyzer": {
                    "type": "custom",
                    "tokenizer": "standard",
                    "filter": ["lowercase", "autocomplete_filter"]
                },
                "autocomplete_search_analyzer": {
                    "type": "custom",
                    "tokenizer": "standard",
                    "filter": ["lowercase"]
                }
            }
        }
    },
    "mappings": {
        "properties": {
            "id": { "type": "integer" },
            "slug": { "type": "keyword" },
            "title": {
                "type": "text",
                "fields": {
                    "keyword": { "type": "keyword" },
                    "autocomplete": {
                        "type": "text",
                        "analyzer": "autocomplete_analyzer",
                        "search_analyzer": "autocomplete_search_analyzer"
                    }
                }
            },
            "description": { "type": "text" },
            "short_description": { "type": "text" },
            "company_name": {
                "type": "text",
                "fields": {
                    "keyword": { "type": "keyword" },
                    "autocomplete": {
                        "type": "text",
                        "analyzer": "autocomplete_analyzer",
                        "search_analyzer": "autocomplete_search_analyzer"
                    }
                }
            },
            "company_slug": { "type": "keyword" },
            "locations": { "type": "keyword" },
            "location_labels": { "type": "keyword" },
            "category_names": { "type": "keyword" },
            "skills": { "type": "keyword" },
            "skills_text": {
                "type": "text",
                "fields": {
                    "autocomplete": {
                        "type": "text",
                        "analyzer": "autocomplete_analyzer",
                        "search_analyzer": "autocomplete_search_analyzer"
                    }
                }
            },
            "job_type": { "type": "keyword" },
            "work_model": { "type": "keyword" },
            "experience_level": { "type": "keyword" },
            "salary_min": { "type": "integer" },
            "salary_max": { "type": "integer" },
            "salary_currency": { "type": "keyword" },
            "salary_is_visible": { "type": "boolean" },
            "is_featured": { "type": "boolean" },
            "is_active": { "type": "boolean" },
            "published_at": { "type": "date" },
            "expires_at": { "type": "date" }
        }
    }
}
```

## Search Architecture Responsibilities

| Component | Responsibility |
| --- | --- |
| `SearchServiceInterface` | App-facing contract for normalized search results and indexing operations |
| `ElasticsearchSearchService` | Production search implementation backed by Elasticsearch |
| `JobSearchQueryBuilder` | Elasticsearch Query DSL construction only |
| `JobIndexDocumentFactory` | Maps relational models into Elasticsearch documents |
| `SearchResultMapper` | Converts backend responses into the normalized app contract |
| `ElasticsearchClient` | Low-level Elasticsearch transport wrapper |
| sync job + observer | After-commit incremental indexing lifecycle |

## Elasticsearch Query Builder (Core Logic)

```php
class JobSearchQueryBuilder
{
    public function build(array $params): array
    {
        $query = trim((string) ($params['q'] ?? ''));

        return [
            'query' => [
                'function_score' => [
                    'query' => [
                        'bool' => [
                            'must' => $query === ''
                                ? [['match_all' => (object) []]]
                                : [[
                                    'multi_match' => [
                                        'query' => $query,
                                        'fields' => [
                                            'title^3',
                                            'skills_text^2',
                                            'company_name^2',
                                            'description',
                                        ],
                                    ],
                                ]],
                            'filter' => [
                                ['term' => ['is_active' => true]],
                                ['range' => ['published_at' => ['lte' => 'now']]],
                                [
                                    'bool' => [
                                        'should' => [
                                            ['bool' => ['must_not' => [['exists' => ['field' => 'expires_at']]]]],
                                            ['range' => ['expires_at' => ['gt' => 'now']]],
                                        ],
                                        'minimum_should_match' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'functions' => [
                        [
                            'exp' => [
                                'published_at' => [
                                    'scale' => '7d',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
```

## Suggest Service (Skeleton)

```php
class JobSuggestService
{
    public function suggest(string $keyword): array
    {
        $response = app('elasticsearch')->search([
            'index' => config('elasticsearch.aliases.job_listings'),
            'body' => [
                'size' => 5,
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        'type' => 'best_fields',
                        'fields' => [
                            'title.autocomplete^3',
                            'skills_text.autocomplete^2',
                            'company_name.autocomplete',
                        ],
                    ],
                ],
            ],
        ]);

        return [
            'items' => collect($response['hits']['hits'] ?? [])
                ->map(fn (array $hit) => $this->mapSuggestionHit($hit))
                ->all(),
        ];
    }

    protected function mapSuggestionHit(array $hit): array
    {
        return [
            'label' => '...',
            'type' => 'job_title',
        ];
    }
}
```

## Index Alias & Zero-Downtime Reindex

### Alias Flow

```text
Before:
app -> job_listings_current -> job_listings_v1

Reindex:
create job_listings_v2
bulk import data into job_listings_v2
validate job_listings_v2
switch alias job_listings_current -> job_listings_v2

After:
app -> job_listings_current -> job_listings_v2
```

### Config shape

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

### Validation before alias switch

- Document count matches MySQL.
- Sample queries return expected normalized results.
- Latency is acceptable.
- Mapping contains the expected fields.

## Operational Workflow

This section is the source of truth for running, rebuilding, and testing Elasticsearch in this project.

### 1. Start local search services

Use this when starting local development, after Docker restarts, or before any Elasticsearch command/test run.

```bash
vendor/bin/sail up -d
vendor/bin/sail artisan es:health --no-interaction
```

- `vendor/bin/sail up -d`
  Starts Laravel, MySQL, Redis, Elasticsearch, and the rest of the local stack.
- `vendor/bin/sail artisan es:health --no-interaction`
  Confirms the app can reach Elasticsearch and reports cluster status.
- Why it matters:
  Most failures in the search flow are not code failures; they are container or Elasticsearch readiness problems. Health-check first.

### 2. Day-to-day development flow

Use this when application code is unchanged at the mapping level and you only need the active alias to reflect current MySQL data.

```bash
vendor/bin/sail artisan es:index-job-listings --no-interaction
```

- What it does:
  Bulk indexes all job listings into the index behind `job_listings_current`.
- When to use it:
  After seeding, after large data imports, or when Elasticsearch drifted and you want to repopulate the active read model without changing mappings.
- Why it matters:
  Observer-driven sync handles normal writes, but bulk indexing is the safe recovery path for large backfills.

### 3. Preferred rebuild flow after mapping or document-shape changes

Use this whenever `config/job_listings_v1_mapping.json`, `JobListing::toSearchDocument()`, or sort/filter fields change.

```bash
vendor/bin/sail artisan es:reindex job_listings_v2_YYYYMMDDHHMMSS --no-interaction
```

- What it does:
  Creates a new versioned index, bulk indexes all jobs into it, refreshes it, and then switches `job_listings_current` to the new index.
- When to use it:
  After mapping changes, field type changes, analyzer changes, or any change that requires a clean rebuild.
- Why it matters:
  Elasticsearch mappings are immutable in practice for this workflow. Reindexing to a fresh versioned index is the safe path.

### 4. Manual create -> index -> swap flow

Use this when you want to inspect the target index before the alias switch or debug one step at a time.

```bash
vendor/bin/sail artisan es:create-index job_listings_v2_YYYYMMDDHHMMSS --no-interaction
vendor/bin/sail artisan es:index-job-listings --index=job_listings_v2_YYYYMMDDHHMMSS --no-interaction
vendor/bin/sail artisan es:switch-alias job_listings_v2_YYYYMMDDHHMMSS --no-interaction
```

- `es:create-index <index>`
  Creates a fresh versioned index using the current mapping file.
- `es:index-job-listings --index=<index>`
  Bulk loads MySQL job data into that specific target index without changing the app alias.
- `es:switch-alias <index>`
  Moves `job_listings_current` to the validated versioned index.
- When to use it:
  During debugging, staged rollout checks, or when you want explicit control over validation before the alias switch.
- Why it matters:
  This is the clearest way to verify each step independently.

### 5. Local test flow

Use these tests when changing Elasticsearch commands, document shape, query building, or normalization logic.

```bash
vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchCommandsTest.php
vendor/bin/sail artisan test --compact tests/Feature/Search/SyncJobListingToElasticsearchTest.php
vendor/bin/sail artisan test --compact tests/Unit/ElasticsearchSearchServiceTest.php
```

- `ElasticsearchCommandsTest.php`
  Covers command-level behavior such as health, alias switching, and reindex orchestration.
- `SyncJobListingToElasticsearchTest.php`
  Covers document mapping and queue-driven sync behavior from Laravel to Elasticsearch.
- `ElasticsearchSearchServiceTest.php`
  Covers query construction, normalization, filters, facets, and sort semantics.
- Why it matters:
  These are the fast checks that catch most Elasticsearch regressions before a live run.

### 6. Live Elasticsearch E2E flow

Use this when you need to prove the real Dockerized Elasticsearch node can create, sync, and delete documents end-to-end.

```bash
docker exec -e ENABLE_LIVE_ES_TESTS=true larasearch-laravel.test-1 php artisan test --compact tests/Feature/Search/JobListingElasticsearchE2ETest.php
```

- What it does:
  Runs the live Elasticsearch feature test file with `ENABLE_LIVE_ES_TESTS=true` inside the PHP container.
- When to use it:
  After mapping changes, alias/reindex flow changes, sync pipeline changes, or before considering the Elasticsearch stack operationally verified.
- Why it matters:
  `vendor/bin/sail artisan test ...` will skip these tests unless that environment variable is present in the PHP process. This invocation is the reliable project-specific way to run them.

### 7. Practical sequence by scenario

- Local startup:
  `vendor/bin/sail up -d` -> `vendor/bin/sail artisan es:health --no-interaction`
- Mapping or document change:
  run `es:reindex <new-versioned-index>` or use the explicit create -> index -> swap sequence
- Data drift only:
  run `es:index-job-listings --no-interaction`
- Debugging command/index state:
  run `es:health`, then use manual create -> index -> swap so each step can be inspected
- Final live verification:
  run `JobListingElasticsearchE2ETest.php` with `ENABLE_LIVE_ES_TESTS=true`

### 8. Command selection summary

| Command | Use it when | Why it matters |
| --- | --- | --- |
| `vendor/bin/sail artisan es:health --no-interaction` | Before any ES workflow | Confirms Elasticsearch is reachable from Laravel |
| `vendor/bin/sail artisan es:index-job-listings --no-interaction` | Active index needs a full backfill | Repopulates the alias target without changing mappings |
| `vendor/bin/sail artisan es:reindex <index> --no-interaction` | Mapping/document shape changed | Preferred zero-downtime rebuild flow |
| `vendor/bin/sail artisan es:create-index <index> --no-interaction` | You want manual control | Creates a fresh versioned target |
| `vendor/bin/sail artisan es:index-job-listings --index=<index> --no-interaction` | You want to validate before swap | Loads data into a non-live target index |
| `vendor/bin/sail artisan es:switch-alias <index> --no-interaction` | New versioned index is ready | Makes the app read from the validated index |
| `docker exec -e ENABLE_LIVE_ES_TESTS=true larasearch-laravel.test-1 php artisan test --compact tests/Feature/Search/JobListingElasticsearchE2ETest.php` | Need real ES end-to-end verification | Proves live create/sync/delete behavior against Dockerized Elasticsearch |

## Consistency Model

- Writes go to MySQL first.
- Search reads come from Elasticsearch.
- Incremental indexing runs after database commit.
- Rebuilds are performed with bulk DB -> ES indexing plus alias swap.
- Temporary drift between MySQL and Elasticsearch is expected and should be treated as eventual consistency, not as a fallback reason to expose provider-specific behavior.
