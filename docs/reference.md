# Technical Reference — Elasticsearch Integration

> Reference code, mapping examples, and implementation patterns for the Larasearch job platform.
> This file is extracted from the original task plan to keep the task checklist clean.

## Elasticsearch Mapping (job_listings_v1)

```json
{
    "settings": {
        "number_of_shards": 1,
        "number_of_replicas": 0
    },
    "mappings": {
        "properties": {
            "id": { "type": "keyword" },
            "title": {
                "type": "text",
                "fields": {
                    "keyword": { "type": "keyword" }
                }
            },
            "description": { "type": "text" },
            "skills": { "type": "keyword" },
            "skills_text": { "type": "text" },
            "company_name": { "type": "keyword" },
            "locations": { "type": "keyword" },
            "salary_min": { "type": "integer" },
            "salary_max": { "type": "integer" },
            "published_at": { "type": "date" },
            "is_active": { "type": "boolean" }
        }
    }
}
```

---

## Autocomplete Mapping Extension

```json
{
    "settings": {
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
            "skills_text": {
                "type": "text",
                "fields": {
                    "autocomplete": {
                        "type": "text",
                        "analyzer": "autocomplete_analyzer",
                        "search_analyzer": "autocomplete_search_analyzer"
                    }
                }
            }
        }
    }
}
```

---

## Laravel: JobSearchService (Skeleton)

```php
class JobSearchService
{
    public function search(array $params)
    {
        $query = (new JobSearchQueryBuilder())->build($params);

        return app('elasticsearch')->search([
            'index' => config('elasticsearch.job_listings_alias'),
            'body' => $query,
        ]);
    }
}
```

---

## Laravel: Query Builder (Core Logic)

```php
class JobSearchQueryBuilder
{
    public function build(array $params): array
    {
        return [
            'query' => [
                'function_score' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'multi_match' => [
                                        'query' => $params['q'] ?? '',
                                        'fields' => [
                                            'title^3',
                                            'skills_text^2',
                                            'description'
                                        ]
                                    ]
                                ]
                            ],
                            'filter' => $this->filters($params)
                        ]
                    ],
                    'functions' => [
                        [
                            'exp' => [
                                'published_at' => [
                                    'scale' => '7d'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function filters($params)
    {
        $filters = [];

        if (!empty($params['skills'])) {
            $filters[] = ['terms' => ['skills' => $params['skills']]];
        }

        if (!empty($params['location'])) {
            $filters[] = ['term' => ['locations' => $params['location']]];
        }

        return $filters;
    }
}
```

---

## Autocomplete Query DSL

```json
{
    "size": 8,
    "query": {
        "multi_match": {
            "query": "lar",
            "type": "best_fields",
            "fields": [
                "title.autocomplete^3",
                "skills_text.autocomplete^2",
                "company_name.autocomplete"
            ]
        }
    }
}
```

---

## Laravel: JobSuggestService (Skeleton)

```php
class JobSuggestService
{
    public function suggest(string $keyword): array
    {
        $response = app('elasticsearch')->search([
            'index' => config('elasticsearch.job_listings_alias'),
            'body' => [
                'size' => 8,
                'query' => [
                    'multi_match' => [
                        'query' => $keyword,
                        'type' => 'best_fields',
                        'fields' => [
                            'title.autocomplete^3',
                            'skills_text.autocomplete^2',
                            'company_name.autocomplete'
                        ]
                    ]
                ]
            ]
        ]);

        return $response['hits']['hits'] ?? [];
    }
}
```

---

## Suggest Endpoint

> **Note:** In this project the suggest endpoint is served via an Inertia controller action, not a standalone REST API route.

```text
GET /jobs/suggest?q=lar
```

### Response format

```json
{
    "data": [
        { "label": "Senior Laravel Backend Engineer", "type": "job_title" },
        { "label": "Laravel", "type": "skill" },
        { "label": "Laravel Developer at Acme Tech", "type": "mixed" }
    ]
}
```

---

## Index Alias & Zero Downtime Reindex

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

### Create Alias

```json
POST /_aliases
{
  "actions": [
    { "add": { "index": "job_listings_v1", "alias": "job_listings_current" } }
  ]
}
```

### Switch Alias

```json
POST /_aliases
{
  "actions": [
    { "remove": { "index": "job_listings_v1", "alias": "job_listings_current" } },
    { "add": { "index": "job_listings_v2", "alias": "job_listings_current" } }
  ]
}
```

### Laravel Config

```php
return [
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
    'job_listings_alias' => env('ELASTICSEARCH_JOB_LISTINGS_ALIAS', 'job_listings_current'),
];
```

### Reindex Command Strategy

```php
class ReindexJobsCommand extends Command
{
    protected $signature = 'es:reindex {version}';

    public function handle()
    {
        $version = $this->argument('version');
        $newIndex = 'job_listings_' . $version;
        $alias = config('elasticsearch.job_listings_alias');

        // 1. create new index with mapping
        // 2. bulk index all jobs into new index
        // 3. validate document count / sample queries
        // 4. switch alias to new index
        // 5. optionally delete old index later
    }
}
```

### Validation Before Alias Switch

- Document count matches DB
- Sample queries return correct results
- Latency is acceptable
- Mapping has expected fields

---

## Key Takeaways

- Mapping determines search quality
- Query builder determines relevance
- Indexing pipeline determines consistency
- Autocomplete significantly improves UX
- Index aliases enable safe production reindexing
