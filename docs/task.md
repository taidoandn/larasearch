# Task Plan

> Goal: build a smart job platform with Elasticsearch-first discovery, then expand toward a real hiring marketplace.
> Source of truth: MySQL → async sync → Elasticsearch.
> Stack: Laravel 12 · React 19 + Inertia v2 · Tailwind v4 · MySQL 8.4 · Elasticsearch 8.x · Docker · Redis

## Phase 0 — Foundation & Search Domain
> Objective: bootable local environment, initial schema, seed data, ES connectivity, search-ready core domain.

### Environment & Docker
- [ ] Add Elasticsearch 8.x + Kibana services to `compose.yaml`
- [ ] Add `ELASTICSEARCH_*` env vars to `.env` / `.env.example`
- [ ] Create `config/elasticsearch.php`
- [ ] Verify all services boot correctly: app, MySQL, Redis, ES, Kibana

### Elasticsearch Client & Ops
- [ ] Install `elasticsearch/elasticsearch`
- [ ] Create `App\Services\ElasticsearchClient`
- [ ] Add `es:health` artisan command
- [ ] Define alias strategy:
  - [ ] `job_listings_v1`
  - [ ] `job_listings_current`

### Phase 0 Schema & Models
- [ ] Create `companies` table + model
- [ ] Create `categories` table + model
- [ ] Create `skills` table + model
- [ ] Create `locations` table + model
- [ ] Create `job_listings` table + model
- [ ] Create pivot `category_job_listing`
- [ ] Create pivot `job_listing_skill`
- [ ] Define Eloquent relationships
- [ ] Create factories for company, category, skill, location, job listing
- [ ] Seed 10k–50k realistic jobs

### Search Architecture Scaffolding
- [ ] Create `SearchServiceInterface`
- [ ] Create `ElasticsearchSearchService`
- [ ] Create `DatabaseSearchService` for benchmark baseline (optional)
- [ ] Register binding in service provider
- [ ] Create `SyncJobListingToElasticsearch` queued job
- [ ] Create `JobListingObserver`
- [ ] Dispatch sync only after DB commit
- [ ] Create bulk command `es:index-job-listings`

## Phase 1 — Search MVP (MBO Focus)
> Objective: prove Elasticsearch value with measurable job discovery experience.

### Learning & Exploration

- [ ] Understand Elasticsearch basics (index, document, shard, analyzer)
- [ ] Learn Query DSL (match, multi_match, bool, filter)
- [ ] Learn mapping types (text vs keyword vs numeric vs date)
- [ ] Explore analyzers (standard, lowercase, custom)

### Elasticsearch Mapping & Indexing
- [ ] Design ES document shape for `job_listings`
- [ ] Map text fields for title / description / company / skills
- [ ] Map keyword fields for filters and facets
- [ ] Map numeric/date fields for range and sort
- [ ] Flatten categories, skills, location, company fields into document
- [ ] Create `es:create-index`
- [ ] Create `es:delete-index`
- [ ] Create `es:reindex`
- [ ] Implement chunked bulk indexing with progress output
- [ ] Add analyzer strategy for autocomplete

### Core Search Features
- [ ] Implement keyword full-text search
- [ ] Implement filters: location, category, job type, salary, remote, experience, skills
- [ ] Implement aggregations/facets
- [ ] Implement sorting: relevance, newest, salary
- [ ] Implement pagination
- [ ] Implement highlighting
- [ ] Add relevance boosting for title, skills, featured, recency

### Suggestions & Related Jobs
- [ ] Choose MVP autocomplete strategy
- [ ] Implement suggestion endpoint
- [ ] Add typo tolerance where safe
- [ ] Implement related jobs query
- [ ] Show related jobs on detail page

### Search UI (React + Inertia)
- [ ] Create search page
- [ ] Create job detail page
- [ ] Build search bar with debounced autocomplete
- [ ] Build filter sidebar
- [ ] Build facet UI
- [ ] Build reusable job card
- [ ] Build pagination and sort selector
- [ ] Add empty/loading states
- [ ] Bind search state to URL params

### Ranking Logic

- [ ] Boost title field
- [ ] Boost skills field
- [ ] Add freshness scoring (function_score / decay)
- [ ] Tune weights (title > skills > description)

### Sorting

- [ ] Implement sort: best_match
- [ ] Implement sort: newest
- [ ] Implement sort: salary desc/asc

### Testing

- [ ] Add feature tests for search controllers
- [ ] Add unit tests for query building
- [ ] Add tests for index sync flows

### Benchmarking (optional)
- [ ] Build `benchmark:search` command
- [ ] Compare Elasticsearch vs MySQL:
  - [ ] keyword search
  - [ ] filtered search
  - [ ] aggregations
  - [ ] autocomplete
- [ ] Define benchmark query set
- [ ] Define relevance evaluation set
- [ ] Track p50 / p95 / result quality

## Phase 2 — Marketplace Core
> Objective: introduce real candidate, employer, and application workflows.

### Authentication & User Roles
- [ ] Create `users` table if not already present in app baseline
- [ ] Add role strategy: candidate, employer, admin
- [ ] Add account status flags
- [ ] Define policies / gates for candidate vs employer access

### Candidate Domain
- [ ] Create `candidate_profiles`
- [ ] Create `user_skills`
- [ ] Create `saved_jobs`
- [ ] Create `followed_companies`
- [ ] Create candidate profile UI
- [ ] Create saved jobs page

### Employer Domain
- [ ] Create `company_users`
- [ ] Add employer dashboard scaffolding
- [ ] Add company member roles: owner, admin, recruiter
- [ ] Create company profile management flow

### Application Domain
- [ ] Create `applications`
- [ ] Create `application_status_histories`
- [ ] Add apply flow
- [ ] Add duplicate-application rule
- [ ] Add application status update flow for recruiters
- [ ] Add candidate application history page

### Notifications
- [ ] Create `notifications`
- [ ] Queue transactional notifications
- [ ] Add in-app notification feed or placeholder API
- [ ] Notify on application submit and status changes

### Company Pages & Reviews
- [ ] Expand public company page data
- [ ] Create `company_reviews`
- [ ] Add moderation-ready review status field
- [ ] Show company jobs and basic ratings

## Phase 3 — Growth Features
> Objective: strengthen retention, discovery, and employer value.

### Saved Searches & Alerts
- [ ] Create `saved_searches`
- [ ] Save search filters as JSON
- [ ] Build email alert worker
- [ ] Add manage alerts UI

### Search Analytics
- [ ] Create `search_queries`
- [ ] Create `job_impressions`
- [ ] Create `job_clicks`
- [ ] Track zero-result searches
- [ ] Add basic analytics dashboard

### Recommendation Prep
- [ ] Design rule-based “jobs for you”
- [ ] Design rule-based “related candidates” placeholder
- [ ] Add configurable relevance boosts
- [ ] Add synonym management
- [ ] Add A/B testing hooks for ranking experiments

### Performance & Operations
- [ ] Add alias-based zero-downtime reindexing
- [ ] Add result caching for hot anonymous queries
- [ ] Add ES cluster health monitoring
- [ ] Monitor query performance and slow logs

## Phase 4 — Resume Intelligence & Matching
> Objective: prepare for AI Match-style workflows without blocking current delivery.

### Resume Domain
- [ ] Create `resumes`
- [ ] Add private file storage strategy
- [ ] Support default resume selection
- [ ] Store parser status and parsed payload fields

### Candidate Discovery
- [ ] Add candidate visibility / consent model
- [ ] Design recruiter-facing candidate search schema
- [ ] Define resume-to-job matching document shape
- [ ] Document semantic/vector search approach for future phase

### Privacy & Security
- [ ] Add signed URL strategy for resume access
- [ ] Add audit trail for sensitive profile/resume views
- [ ] Add rate limits for employer contact workflows

## Milestone Exit Criteria
### Exit Phase 1
- Search UI works end-to-end
- ES beats DB baseline on agreed query set
- Reindex and sync flows are reliable

### Exit Phase 2
- Candidate can apply to jobs
- Recruiter can review applications
- Core notifications are working

### Exit Phase 3
- Saved searches and analytics are live
- Relevance tuning has measurable inputs

### Exit Phase 4
- Resume model supports future matching
- Privacy boundaries are clearly enforced

---

## 🚀 Optional Stretch Tasks (If Time Allows)

- [ ] Add autocomplete (search-as-you-type)
- [ ] Add synonym support (e.g., js → javascript)
- [ ] Add typo tolerance tuning
- [ ] Add highlight in results
- [ ] Add aggregations for facet counts

---

## 📈 Long-term Tasks (Post-MBO)

### Recommendation System

- [ ] Track user search & click events
- [ ] Build job recommendation service

### Candidate Search

- [ ] Index CV/resume data
- [ ] Build recruiter-side search

### Analytics

- [ ] Track search CTR
- [ ] Track search-to-apply conversion
- [ ] Identify zero-result queries

### Infrastructure

- [ ] Add Elasticsearch monitoring
- [ ] Implement index aliasing
- [ ] Add retry mechanism for indexing

---

## ✅ Definition of Done (MBO)

- [ ] Elasticsearch fully integrated with Laravel
- [ ] Job search API works with keyword + filters
- [ ] UI fully functional for search experience
- [ ] Dataset ≥ 10,000 jobs indexed
- [ ] Performance benchmark documented
- [ ] Relevance evaluation completed
- [ ] Demo ready for presentation

---

# PRODUCTION-READY TECHNICAL ADD-ON

## Elasticsearch Mapping (jobs_index_v1.json)

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

## Laravel: JobSearchService

```php
class JobSearchService
{
    public function search(array $params)
    {
        $query = (new JobSearchQueryBuilder())->build($params);

        return app('elasticsearch')->search([
            'index' => 'jobs_v1',
            'body' => $query
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

## Reindex Command (Simplified)

```php
php artisan jobs:reindex
```

Core idea:

- chunk DB
- transform → ES doc
- bulk insert

---

## Key Takeaways

- Mapping quyết định phần lớn chất lượng search
- Query builder quyết định relevance
- Indexing pipeline quyết định consistency
- Autocomplete giúp UX tốt hơn rất nhiều
- Index alias giúp reindex an toàn trong production

=> Đây là 5 core bạn nên master trong MBO

---

## Autocomplete / Search-as-you-type

### Mục tiêu

Bổ sung trải nghiệm gõ đến đâu gợi ý đến đó cho:

- job title
- skill
- company name

Autocomplete giúp:

- giảm typo
- tăng tốc độ tìm kiếm
- dẫn user vào đúng query phổ biến
- tăng CTR từ search box

### Mapping đề xuất cho autocomplete

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

### Query DSL cho autocomplete

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

### Laravel endpoint đề xuất

```text
GET /api/jobs/suggest?q=lar
```

### Response format đề xuất

```json
{
    "data": [
        {
            "label": "Senior Laravel Backend Engineer",
            "type": "job_title"
        },
        {
            "label": "Laravel",
            "type": "skill"
        },
        {
            "label": "Laravel Developer at Acme Tech",
            "type": "mixed"
        }
    ]
}
```

### Laravel service skeleton

```php
class JobSuggestService
{
    public function suggest(string $keyword): array
    {
        $response = app('elasticsearch')->search([
            'index' => 'jobs_v1',
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

### React UX flow đề xuất

- debounce 250ms
- chỉ gọi suggest khi user gõ từ 2 ký tự trở lên
- arrow up/down để chọn
- enter để submit search
- click suggestion để fill query rồi search

### Task bổ sung cho Autocomplete / Search-as-you-type

- [ ] Add autocomplete analyzer vào mapping
- [ ] Reindex dữ liệu sau khi đổi mapping
- [ ] Tạo endpoint `/api/jobs/suggest`
- [ ] Tạo `JobSuggestService`
- [ ] Build `AutocompleteSearchBar.tsx`
- [ ] Add debounce + keyboard navigation
- [ ] Test query gõ dở như `lar`, `rea`, `jav`

---

## Index Alias + Zero Downtime Reindex

### Mục tiêu

Cho phép thay đổi mapping / reindex production mà không downtime và không làm hỏng search đang chạy.

### Vấn đề nếu không dùng alias

Nếu app search trực tiếp vào `jobs_v1`:

- khi cần đổi mapping phải drop index cũ
- search có thể lỗi hoặc mất dữ liệu tạm thời
- rollout rất rủi ro

### Cách làm đúng

App luôn search qua alias cố định, ví dụ:

- alias: `jobs_current`
- index thật: `jobs_v1`, `jobs_v2`, `jobs_v3`

Khi reindex:

1. tạo index mới `jobs_v2`
2. apply mapping mới
3. bulk index toàn bộ data vào `jobs_v2`
4. test `jobs_v2`
5. switch alias `jobs_current` từ `jobs_v1` sang `jobs_v2`
6. xóa index cũ sau nếu cần

### Alias flow

```text
Before:
app -> jobs_current -> jobs_v1

Reindex:
create jobs_v2
bulk import data into jobs_v2
validate jobs_v2
switch alias jobs_current -> jobs_v2

After:
app -> jobs_current -> jobs_v2
```

### Create index + alias example

```json
POST /_aliases
{
  "actions": [
    {
      "add": {
        "index": "jobs_v1",
        "alias": "jobs_current"
      }
    }
  ]
}
```

### Switch alias example

```json
POST /_aliases
{
  "actions": [
    {
      "remove": {
        "index": "jobs_v1",
        "alias": "jobs_current"
      }
    },
    {
      "add": {
        "index": "jobs_v2",
        "alias": "jobs_current"
      }
    }
  ]
}
```

### Laravel config best practice

```php
return [
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
    'jobs_alias' => env('ELASTICSEARCH_JOBS_ALIAS', 'jobs_current'),
];
```

### Update JobSearchService để dùng alias

```php
class JobSearchService
{
    public function search(array $params)
    {
        $query = (new JobSearchQueryBuilder())->build($params);

        return app('elasticsearch')->search([
            'index' => config('services.elasticsearch.jobs_alias'),
            'body' => $query,
        ]);
    }
}
```

### Reindex command strategy đề xuất

```php
class ReindexJobsCommand extends Command
{
    protected $signature = 'jobs:reindex {version}';

    public function handle()
    {
        $version = $this->argument('version');
        $newIndex = 'jobs_' . $version;
        $alias = config('services.elasticsearch.jobs_alias');

        // 1. create new index with mapping
        // 2. bulk index all jobs into new index
        // 3. validate document count / sample queries
        // 4. switch alias to new index
        // 5. optionally delete old index later
    }
}
```

### Validate trước khi switch alias

Cần check ít nhất:

- số lượng document có khớp DB không
- query mẫu có trả về đúng không
- latency có ổn không
- mapping có đúng field không

### Task bổ sung cho Index Alias + Zero Downtime Reindex

- [ ] Đổi app search từ `jobs_v1` sang alias `jobs_current`
- [ ] Tạo command tạo index version mới
- [ ] Tạo command bulk reindex vào index mới
- [ ] Tạo command switch alias
- [ ] Thêm bước validate trước alias switch
- [ ] Viết rollback guide: switch alias về index cũ
- [ ] Test flow `jobs_v1 -> jobs_v2`

---
