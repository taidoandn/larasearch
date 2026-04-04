# Task Plan

> Goal: build a smart job platform with Elasticsearch-first discovery, then expand toward a real hiring marketplace.
> Source of truth: MySQL → async sync → Elasticsearch.
> Stack: Laravel 12 · React 19 + Inertia v2 · Tailwind v4 · MySQL 8.4 · Elasticsearch 8.x · Docker · Redis

## Phase 0 — Foundation & Search Domain
> Objective: bootable local environment, initial schema, seed data, ES connectivity, search-ready core domain.

### Learning & Exploration
- [ ] Understand Elasticsearch basics (index, document, shard, analyzer)
- [ ] Learn Query DSL (match, multi_match, bool, filter)
- [ ] Learn mapping types (text vs keyword vs numeric vs date)
- [ ] Explore analyzers (standard, lowercase, custom, edge_ngram for autocomplete)

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
  - [ ] `job_listings_v1` (versioned index)
  - [ ] `job_listings_current` (alias used by app)
- [ ] Create `es:create-index` command
- [ ] Create `es:delete-index` command
- [ ] Create `es:switch-alias` command

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
- [ ] Create bulk command `es:index-job-listings` with chunked progress

## Phase 1 — Search MVP (MBO Focus)
> Objective: prove Elasticsearch value with measurable job discovery experience.

### Elasticsearch Mapping & Indexing
- [ ] Create versioned ES mapping JSON file (e.g. `config/elasticsearch/job_listings_mapping.json`)
- [ ] Design ES document shape for `job_listings`
- [ ] Map text fields for title / description / company / skills
- [ ] Map keyword fields for filters and facets
- [ ] Map numeric/date fields for range and sort
- [ ] Add autocomplete analyzer (edge_ngram) for title, company, skills
- [ ] Flatten categories, skills, location, company fields into document
- [ ] Implement `es:reindex` command with alias swap and validation

### Core Search Features
- [ ] Implement keyword full-text search (multi_match)
- [ ] Implement filters: location, category, job type, salary range, work model, experience level, skills
- [ ] Implement aggregations/facets for filter counts
- [ ] Implement sorting: best match, newest, salary asc/desc
- [ ] Implement pagination
- [ ] Implement highlighting for matched terms
- [ ] Add relevance boosting:
  - [ ] Boost title field (highest)
  - [ ] Boost skills field (medium)
  - [ ] Description match (lowest)
  - [ ] Freshness decay via `function_score`
  - [ ] Featured job boost
- [ ] Create `SearchRequest` FormRequest for input validation

### Suggestions & Related Jobs
- [ ] Implement autocomplete/suggest endpoint
- [ ] Create `JobSuggestService`
- [ ] Add typo tolerance where safe
- [ ] Implement related jobs query (more-like-this or similar)

### Search UI (React + Inertia)
- [ ] Create search page (`resources/js/pages/Jobs/Search.tsx`)
- [ ] Create job detail page
- [ ] Build search bar with debounced autocomplete + keyboard navigation
- [ ] Build filter sidebar
- [ ] Build facet count UI
- [ ] Build reusable job card component
- [ ] Build pagination and sort selector
- [ ] Build active filter chips
- [ ] Add empty/loading/skeleton states
- [ ] Bind search state to URL params

### Testing
- [ ] Add feature tests for search controller
- [ ] Add unit tests for query building
- [ ] Add tests for index sync flows
- [ ] Test autocomplete queries (partial inputs like `lar`, `rea`, `jav`)

### Benchmarking (optional)
- [ ] Build `benchmark:search` command
- [ ] Compare Elasticsearch vs MySQL: keyword, filtered, aggregations
- [ ] Define benchmark query set and relevance evaluation set
- [ ] Track p50 / p95 / result quality

## Phase 2 — Marketplace Core
> Objective: introduce real candidate, employer, and application workflows.

### Authentication & User Roles
- [ ] Create `users` table if not already present in app baseline
- [ ] Add role strategy: candidate, employer, admin
- [ ] Add account status flags
- [ ] Define policies / gates for candidate vs employer access
- [ ] Choose auth approach (Fortify / Breeze — deferred decision)

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

### Notifications (Laravel Built-in)
- [ ] Run `php artisan notifications:table` and migrate
- [ ] Create notification classes (e.g. `ApplicationSubmitted`, `ApplicationStatusChanged`)
- [ ] Queue transactional notifications via `database` + `mail` channels
- [ ] Add in-app notification feed or placeholder
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
- [ ] Design rule-based "jobs for you"
- [ ] Design rule-based "related candidates" placeholder
- [ ] Add configurable relevance boosts
- [ ] Add synonym management
- [ ] Add A/B testing hooks for ranking experiments

### Performance & Operations
- [ ] Add alias-based zero-downtime reindexing with validation
- [ ] Add result caching for hot anonymous queries
- [ ] Add ES cluster health monitoring
- [ ] Monitor query performance and slow logs
- [ ] Write rollback guide for alias switching

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

---

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

## ✅ Definition of Done (MBO)

- [ ] Elasticsearch fully integrated with Laravel
- [ ] Job search works with keyword + filters via Inertia pages
- [ ] UI fully functional for search experience
- [ ] Dataset ≥ 10,000 jobs indexed
- [ ] Performance benchmark documented
- [ ] Relevance evaluation completed
- [ ] Demo ready for presentation
