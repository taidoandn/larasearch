# Technical Reference — Search Contract & Elasticsearch Integration

> Canonical technical reference for the Larasearch search contract, Elasticsearch read model, and search service responsibilities.

## Canonical Rules

- MySQL is the transactional source of truth.
- Elasticsearch is the user-facing search read model.
- Controllers and UI consume an app-owned normalized contract.
- `DatabaseSearchService` exists only for benchmark and local comparison work. It does not define product behavior.
- Elasticsearch helper fields such as `skills_text` are internal implementation details and must not leak into controller or UI contracts.

## Current Implementation Notes

- The authenticated `/search` page is served by `SearchResultsController` and rendered through the Inertia page at `resources/js/pages/search-results.tsx`.
- Query validation is handled by `SearchRequest`.
- `ElasticsearchSearchService` normalizes backend responses into the canonical search contract before the page consumes them.

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

This is the single public result shape consumed by controllers, benchmarks, and UI.

```json
{
    "items": [
        {
            "id": 1,
            "slug": "senior-laravel-backend-engineer-acme-tech",
            "title": "Senior Laravel Backend Engineer",
            "company": {
                "name": "Acme Tech",
                "slug": "acme-tech"
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
- `facets` may be empty objects or empty arrays when not requested yet, but the top-level key should remain stable.
- `highlight` is normalized application output, not raw Elasticsearch highlight payloads.
- Backends must return this contract even when their internal query engines differ.

## Suggest Contract

The suggest endpoint is served via a Laravel controller action for the Inertia search experience.

```text
GET /jobs/suggest?q=lar
```

```json
{
    "items": [
        { "label": "Senior Laravel Backend Engineer", "type": "job_title" },
        { "label": "Laravel", "type": "skill" },
        { "label": "Acme Tech", "type": "company" }
    ]
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
| `locations.display_name` | `locations` | `locations`, `primary_location` |
| `categories.name` | `category_names` | facet source |
| `skills.name` | `skills` | `skills` |
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
    "locations": ["da-nang"],
    "location_labels": ["Da Nang"],
    "category_names": ["Backend"],
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
            "id": { "type": "keyword" },
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
| `DatabaseSearchService` | Benchmark-only baseline for comparison against Elasticsearch |
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
                'size' => 8,
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

## Consistency Model

- Writes go to MySQL first.
- Search reads come from Elasticsearch.
- Incremental indexing runs after database commit.
- Rebuilds are performed with bulk DB -> ES indexing plus alias swap.
- Temporary drift between MySQL and Elasticsearch is expected and should be treated as eventual consistency, not as a fallback reason to expose provider-specific behavior.
