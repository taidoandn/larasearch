# Search MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver the authenticated Search MVP end-to-end: Elasticsearch-backed job search, suggest, related jobs, and the React/Inertia jobs browsing experience with reliable reindex and verification flows.

**Architecture:** MySQL remains the transactional source of truth while Elasticsearch serves as the read model behind `SearchServiceInterface`. The Search MVP is split into index/mapping operations, normalized search contracts, suggest/detail behavior, and UI orchestration so backend and frontend can evolve independently without leaking Elasticsearch internals into the page layer.

**Operational Notes:** The app reads through the `job_listings_current` alias and rebuilds search with versioned indices plus alias swap. Mapping or document-shape changes must be rolled out through `es:reindex` or the explicit create -> index -> switch flow documented in `docs/reference.md`.

**Tech Stack:** Laravel 12, Laravel Sail, Pest 4, Elasticsearch 8.x, MySQL 8.4, Redis, Inertia v2, React 19, Tailwind v4

---

### Task 1: Versioned Index Mapping and Reindex Operations

**Files:**
- Create: `app/Console/Commands/ElasticsearchReindexCommand.php`
- Modify: `config/elasticsearch.php`
- Create: `config/job_listings_v1_mapping.json`
- Modify: `app/Models/JobListing.php`
- Modify: `tests/Feature/Configuration/ElasticsearchConfigurationTest.php`
- Modify: `tests/Feature/Console/ElasticsearchCommandsTest.php`
- Modify: `tests/Feature/Database/JobSearchDomainTest.php`
- Modify: `docs/reference.md`

- [x] **Step 1: Define the versioned mapping and alias contract**

Implemented:
- `job_listings_v1` mapping file created in `config/job_listings_v1_mapping.json`
- `job_listings_current` used as the application alias
- numeric/date/filter/sort fields mapped in the Elasticsearch document

- [x] **Step 2: Implement create/delete/switch/reindex command coverage**

Run:
```bash
vendor/bin/sail artisan test --compact tests/Feature/Console/ElasticsearchCommandsTest.php
```
Expected: PASS with command behavior covering alias and reindex orchestration.

- [x] **Step 3: Support zero-downtime rebuild flow**

Implemented:
- `es:create-index`
- `es:index-job-listings`
- `es:switch-alias`
- `es:reindex`

- [x] **Step 4: Verify the live index flow**

Run:
```bash
vendor/bin/sail artisan es:health --no-interaction
vendor/bin/sail artisan es:reindex job_listings_v2_YYYYMMDDHHMMSS --no-interaction
docker exec -e ENABLE_LIVE_ES_TESTS=true larasearch-laravel.test-1 php artisan test --compact tests/Feature/Search/JobListingElasticsearchE2ETest.php
```
Expected: Elasticsearch reachable, alias swapped to a fresh versioned index, live E2E passes.

### Task 2: Canonical Search Service and Query Contract

**Files:**
- Modify: `app/Services/ElasticsearchSearchService.php`
- Modify: `app/Http/Requests/SearchRequest.php`
- Modify: `app/Http/Controllers/JobsController.php`
- Create: `app/Services/JobSearchFilters.php`
- Modify: `tests/Unit/ElasticsearchSearchServiceTest.php`
- Modify: `tests/Feature/Jobs/JobsControllerTest.php`
- Modify: `docs/reference.md`

- [x] **Step 1: Normalize the search request contract**

Implemented:
- canonical filters for `q`, `location`, `category`, `skills`, `job_type`, `work_model`, `experience_level`, `salary_min`, `salary_max`, `sort`, `page`, `per_page`
- shared normalization in `JobSearchFilters`
- controller validation through `SearchRequest`

- [x] **Step 2: Build Elasticsearch query, facets, sort, and highlight behavior**

Implemented:
- keyword search via `multi_match`
- filter/facet aggregation support
- best match / newest / salary asc / salary desc sorting
- normalized `highlight` output
- deterministic sort tie-breaking using integer `id`

- [x] **Step 3: Tighten search semantics from review feedback**

Implemented:
- salary filtering now uses overlap semantics
- location filtering matches slug or human-readable label text
- canonical list contract includes `application_url` and `company.website`

- [x] **Step 4: Verify backend search contract**

Run:
```bash
vendor/bin/sail artisan test --compact tests/Unit/ElasticsearchSearchServiceTest.php tests/Feature/Jobs/JobsControllerTest.php
```
Expected: PASS with normalized filters, facets, sort clauses, and canonical result payload assertions.

### Task 3: Suggest Endpoint and Related Jobs

**Files:**
- Create: `app/Http/Controllers/JobSuggestController.php`
- Create: `app/Services/JobSuggestService.php`
- Modify: `app/Http/Controllers/JobShowController.php`
- Modify: `tests/Unit/JobSuggestServiceTest.php`
- Modify: `tests/Feature/Jobs/JobsControllerTest.php`
- Modify: `tests/Feature/JobsPageTest.php`
- Modify: `tests/Feature/Search/JobListingElasticsearchE2ETest.php`
- Modify: `docs/reference.md`

- [x] **Step 1: Add authenticated suggest endpoint**

Implemented:
- `jobs.suggest` route
- JSON controller response
- request validation for `q`
- authenticated access behavior

- [x] **Step 2: Implement suggestion relevance rules**

Implemented:
- title / skills / company autocomplete query
- safe typo-tolerant matching
- capped suggestion response
- normalized `label` + `type` contract

- [x] **Step 3: Implement dedicated job detail and related jobs payload**

Implemented:
- `jobs.show` page contract
- related jobs heuristic using shared categories/skills with same-company fallback
- compact validated search context for back-navigation

- [x] **Step 4: Verify suggest and detail flows**

Run:
```bash
vendor/bin/sail artisan test --compact tests/Unit/JobSuggestServiceTest.php tests/Feature/Jobs/JobsControllerTest.php tests/Feature/JobsPageTest.php
```
Expected: PASS with suggest auth/validation coverage, typo-tolerance coverage, show-page payload assertions, and related-jobs coverage.

### Task 4: Search UI in React + Inertia

**Files:**
- Modify: `resources/js/pages/jobs/index.tsx`
- Modify: `resources/js/pages/jobs/show.tsx`
- Create: `resources/js/features/jobs/components/jobs-filters.tsx`
- Create: `resources/js/features/jobs/components/jobs-results-list.tsx`
- Create: `resources/js/features/jobs/components/jobs-results-toolbar.tsx`
- Create: `resources/js/features/jobs/components/job-summary-sheet.tsx`
- Create: `resources/js/features/jobs/components/job-header.tsx`
- Create: `resources/js/features/jobs/components/job-detail-info.tsx`
- Create: `resources/js/features/jobs/components/job-related-list.tsx`
- Create: `resources/js/features/jobs/hooks/use-job-search.ts`
- Create: `resources/js/features/jobs/hooks/use-job-suggestions.ts`
- Create: `resources/js/features/jobs/api/fetch-job-suggestions.ts`
- Modify: `resources/js/features/jobs/types/index.ts`
- Modify: `docs/ui/screens/jobs-index/DESIGN.md`
- Modify: `docs/ui/screens/jobs-show/DESIGN.md`
- Modify: `docs/ui/screens/job-summary-sheet/DESIGN.md`

- [x] **Step 1: Build the jobs index and summary-sheet flow**

Implemented:
- index page composition under `resources/js/pages/jobs/index.tsx`
- summary sheet for row selection
- reusable results list, toolbar, and pagination

- [x] **Step 2: Build the jobs detail page**

Implemented:
- full detail route/page
- metadata sidebar
- apply actions
- related opportunities list

- [x] **Step 3: Complete filter, suggest, and URL-sync interactions**

Implemented:
- debounced autocomplete with keyboard navigation
- applied filter chips
- search submission via real form behavior
- draft/applied filter consistency improvements
- route-helper based suggestion fetch

- [x] **Step 4: Verify frontend correctness**

Run:
```bash
vendor/bin/sail npm run types:check
vendor/bin/sail npm run lint:check
```
Expected: PASS with no TypeScript or ESLint errors.

### Task 5: Search MVP Completion and Operational Verification

**Files:**
- Modify: `docs/task.md`
- Modify: `docs/project-document.md`
- Modify: `docs/reference.md`
- Create: `docs/superpowers/plans/2026-04-10-search-mvp-phase.md`

- [x] **Step 1: Align Search MVP docs with the implemented system**

Implemented:
- canonical Elasticsearch runtime workflow documented in `docs/reference.md`
- jobs index/show design docs updated to current component structure
- list/detail contract drift corrected in docs

- [x] **Step 2: Verify the project checklist matches the implemented milestone**

Implemented:
- Search MVP completed items reflected in `docs/task.md`
- done items checked at the milestone level where sub-items are already complete

- [x] **Step 3: Run the focused Search MVP verification suite**

Run:
```bash
vendor/bin/sail artisan test --compact tests/Unit/ElasticsearchSearchServiceTest.php tests/Unit/JobSuggestServiceTest.php tests/Feature/Jobs/JobsControllerTest.php tests/Feature/JobsPageTest.php tests/Feature/Database/JobSearchDomainTest.php
vendor/bin/sail npm run types:check
vendor/bin/sail npm run lint:check
```
Expected: PASS.

- [x] **Step 4: Run live Elasticsearch verification**

Run:
```bash
vendor/bin/sail up -d
vendor/bin/sail artisan es:health --no-interaction
docker exec -e ENABLE_LIVE_ES_TESTS=true larasearch-laravel.test-1 php artisan test --compact tests/Feature/Search/JobListingElasticsearchE2ETest.php
```
Expected: Sail services up, Elasticsearch `green`, and live E2E search tests passing.
