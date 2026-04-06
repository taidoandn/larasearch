# Task Plan

> Goal: build a smart job platform with Elasticsearch-first discovery, then expand toward a real hiring marketplace.
> Source of truth: MySQL → async sync → Elasticsearch.
> Stack: Laravel 12 · React 19 + Inertia v2 · Tailwind v4 · MySQL 8.4 · Elasticsearch 8.x · Docker · Redis

## Phase Definitions

- Phase 0: Search Core
- Milestone: Search MVP (built on Phase 0)
- Phase 1: Marketplace Core
- Phase 2: Search Analytics
- Phase 3: Resume & Matching Readiness

## Phase 0 — Search Core
> Objective: bootable local environment, initial schema, seed data, ES connectivity, search-ready core domain.

### Learning & Exploration
- [ ] Understand Elasticsearch basics (index, document, shard, analyzer)
- [ ] Learn Query DSL (match, multi_match, bool, filter)
- [ ] Learn mapping types (text vs keyword vs numeric vs date)
- [ ] Explore analyzers (standard, lowercase, custom, edge_ngram for autocomplete)

### Environment & Docker
- [x] Add Elasticsearch 8.x + Kibana services to `compose.yaml`
- [x] Add `ELASTICSEARCH_*` env vars to `.env` / `.env.example`
- [x] Create `config/elasticsearch.php`
- [x] Verify all services boot correctly: app, MySQL, Redis, ES, Kibana

### Elasticsearch Client & Ops
- [x] Install `elasticsearch/elasticsearch`
- [x] Create `App\Services\ElasticsearchClient`
- [x] Add `es:health` artisan command
- [ ] Define alias strategy:
  - [x] `job_listings_v1` (versioned index)
  - [x] `job_listings_current` (alias used by app)
- [x] Create `es:create-index` command
- [x] Create `es:delete-index` command
- [x] Create `es:switch-alias` command
- [x] Support bulk indexing to an explicit versioned index before alias switch

### Phase 0 Schema & Models
- [x] Create `companies` table + model
- [x] Create `categories` table + model
- [x] Create `skills` table + model
- [x] Create `locations` table + model
- [x] Create `job_listings` table + model
- [x] Create pivot `category_job_listing`
- [x] Create pivot `job_listing_skill`
- [x] Define Eloquent relationships
- [x] Create factories for company, category, skill, location, job listing
- [x] Seed at least 5k realistic jobs

### Search Architecture Scaffolding
- [x] Create `SearchServiceInterface`
- [x] Create `ElasticsearchSearchService`
- [x] Create `DatabaseSearchService` for benchmark baseline only
- [x] Register binding in service provider
- [x] Create `SyncJobListingToElasticsearch` queued job
- [x] Create `JobListingObserver`
- [x] Dispatch sync only after DB commit
- [x] Create bulk command `es:index-job-listings` with chunked progress
- [x] Dispatch delete syncs before company cascade deletes so Elasticsearch does not retain stale listings
- [ ] TODO later: reindex denormalized taxonomy/admin edits when category, skill, or job-listing pivot edits become part of the supported write flows

## Search MVP Milestone (MBO Focus)
> Objective: prove Elasticsearch value with measurable job discovery experience.

Authenticated users access the search experience in the current MVP scope.

The canonical search payload and Elasticsearch document shape are defined in `docs/reference.md`.

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
- [ ] Implement the canonical `items` / `pagination` / `facets` / `sort` result contract
- [ ] Implement highlighting for matched terms
- [ ] Add relevance boosting:
  - [ ] Boost title field (highest)
  - [ ] Boost skills field (medium)
  - [ ] Description match (lowest)
  - [ ] Freshness decay via `function_score`
  - [ ] Featured job boost
- [x] Create `SearchRequest` FormRequest for input validation

### Suggestions & Related Jobs
- [ ] Implement autocomplete/suggest endpoint
- [ ] Create `JobSuggestService`
- [ ] Add typo tolerance where safe
- [ ] Implement related jobs query (more-like-this or similar)

### Search UI (React + Inertia)
- [x] Create search page (`resources/js/pages/search-results.tsx`)
- [ ] Create job detail page
- [ ] Build search bar with debounced autocomplete + keyboard navigation
- [ ] Build filter sidebar
- [ ] Build facet count UI
- [ ] Build reusable job card component
- [ ] Build pagination and sort selector
- [ ] Build active filter chips
- [ ] Add empty/loading/skeleton states
- [ ] Bind search state to URL params
- [ ] Keep search consumers isolated from raw Elasticsearch response shapes and internal ES helper fields

### Testing
- [x] Add feature tests for search controller
- [ ] Add unit tests for query building
- [x] Add tests for index sync flows
- [ ] Test autocomplete queries (partial inputs like `lar`, `rea`, `jav`)
- [x] Add live Elasticsearch E2E coverage for create/sync and delete-sync flows

### Benchmarking (optional)
- [ ] Build `benchmark:search` command
- [ ] Compare Elasticsearch vs MySQL baseline: keyword, filtered, aggregations
- [ ] Define benchmark query set and relevance evaluation set
- [ ] Track p50 / p95 / result quality

## Phase 1 — Marketplace Core
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
- [ ] Run `vendor/bin/sail artisan notifications:table --no-interaction` and migrate
- [ ] Create notification classes (e.g. `ApplicationSubmitted`, `ApplicationStatusChanged`)
- [ ] Queue transactional notifications via `database` + `mail` channels
- [ ] Add in-app notification feed or placeholder
- [ ] Notify on application submit and status changes

### Company Pages & Reviews
- [ ] Expand public company page data
- [ ] Create `company_reviews`
- [ ] Add moderation-ready review status field
- [ ] Show company jobs and basic ratings

## Phase 2 — Search Analytics
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

## Phase 3 — Resume & Matching Readiness
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

### Exit Search MVP Milestone
- Search UI works end-to-end
- ES beats DB baseline on agreed query set
- Reindex and sync flows are reliable

### Exit Phase 1
- Candidate can apply to jobs
- Recruiter can review applications
- Core notifications are working

### Exit Phase 2
- Saved searches and analytics are live
- Relevance tuning has measurable inputs

### Exit Phase 3
- Resume model supports future matching
- Privacy boundaries are clearly enforced

---

## ✅ Definition of Done (MBO)

- [ ] Elasticsearch fully integrated with Laravel
- [ ] Job search works with keyword + filters via Inertia pages
- [ ] UI fully functional for search experience
- [ ] Dataset ≥ 5,000 jobs indexed
- [ ] Performance benchmark documented
- [ ] Relevance evaluation completed
- [ ] Demo ready for presentation
