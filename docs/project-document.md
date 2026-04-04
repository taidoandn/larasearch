# MBO Project Document: Elasticsearch for Job Platform

## 1. Executive Summary

This document defines a practical Management by Objectives (MBO) plan to research, validate, and implement Elasticsearch for a job platform built with Laravel, InertiaJS, ReactJS, TailwindCSS, shadcn/ui, Docker, and MySQL/PostgreSQL.

The short-term goal is to deliver a working MVP focused on **job search** with meaningful improvements in search relevance, speed, filterability, and extensibility. The long-term goal is to evolve the search foundation into an intelligent talent platform capable of powering recommendations, CV-to-job matching, employer-side candidate discovery, analytics, and ranking optimization.

---

## Companion Documentation

| File | Description |
|------|-------------|
| [prd.md](prd.md) | Product Requirements Document — canonical phase definitions, scope, user flows, functional/non-functional requirements, KPIs, and release strategy |
| [erd.md](erd.md) | Entity-Relationship Diagram — text-based domain model with phase-based relationship maps and cardinality summary |
| [schema.md](schema.md) | Database Schema — phased relational table definitions, column specs, indexes, and implementation notes |
| [task.md](task.md) | Task Plan — phase-aligned execution checklist with milestone exit criteria and definition of done |
| [reference.md](reference.md) | Technical Reference — Elasticsearch mapping examples, service skeletons, query DSL samples, and alias flow patterns |

---

## 2. Short-term MBO (2–4 weeks)

## 2.1 Objective

Build and validate an Elasticsearch-based job search MVP that supports keyword search, structured filters, and ranking logic suitable for a production-oriented job platform.

## 2.2 Business Goal

Enable users to find relevant jobs faster and more accurately than with database `LIKE` queries or naive filtering, while creating a scalable foundation for future search and recommendation features.

## 2.3 Scope of MVP

The MVP will focus only on the core candidate search journey:

- Search jobs by keyword
- Filter by skill, location, salary range
- Sort and rank by relevance and freshness
- Sync job data from Laravel application database to Elasticsearch
- Expose search results through REST API
- Render results in React/Inertia UI
- Measure search latency and relevance quality

Out of scope for short-term MBO:

- Candidate search / resume search
- Personalized recommendations
- AI/ML semantic search
- Employer-side ATS workflows
- Complex analytics dashboards
- Multi-language NLP enhancements

## 2.4 Success Criteria

The short-term MBO is considered successful if the team can demonstrate all of the following:

### Functional success

- Users can search jobs by free-text keyword
- Users can filter by skill, city/location, and salary range
- Search results are ranked in a way that feels better than plain database search
- Search API can support pagination and sorting

### Technical success

- Elasticsearch runs locally through Docker
- Laravel can index and re-index jobs reliably
- Search endpoint response time is consistently low under test dataset volume
- Search mapping supports future extension

### Measurement success

- Search latency for common queries is acceptable, ideally under 150–300 ms in local/staging conditions
- Test data volume reaches at least 10,000 jobs, ideally 10,000–50,000 jobs
- Team documents relevance findings and trade-offs

## 2.5 Deliverables

By the end of the short-term MBO, the following deliverables should exist:

1. Dockerized Elasticsearch setup
2. Laravel indexing pipeline for jobs
3. Job search API endpoint using Elasticsearch
4. React/Inertia search UI with filters
5. Seed dataset with realistic fake jobs
6. Index mapping and query design documentation
7. Relevance testing notes
8. Performance benchmark summary
9. Final demo showing end-to-end job search flow

## 2.6 Suggested Timeline

### Week 1 — Research and environment setup

- Understand Elasticsearch fundamentals: index, shard, document, analyzer, mapping, query DSL
- Define search scope for job platform
- Set up Docker with Laravel, database, and Elasticsearch
- Create initial job index mapping
- Prepare sample dataset structure

### Week 2 — Data modeling and indexing

- Design relational schema for jobs, companies, skills, locations
- Design Elasticsearch document model for jobs
- Build seeder for realistic job data
- Implement indexing command / job / listener in Laravel
- Add bulk reindex flow

### Week 3 — Search API and ranking

- Build search endpoint in Laravel
- Implement keyword matching + filters + pagination
- Add scoring weights for title, skills, company, freshness
- Add sort modes such as newest and salary descending
- Validate with sample search scenarios

### Week 4 — Frontend integration and evaluation

- Build search page UI in React/Inertia
- Add keyword bar, filter panels, result list, empty state
- Measure response times
- Compare search quality against DB-based search
- Write learning summary and next-phase recommendations

## 2.7 Key Risks and Mitigations

| Risk                                          | Impact                        | Mitigation                                                                      |
| --------------------------------------------- | ----------------------------- | ------------------------------------------------------------------------------- |
| Elasticsearch concepts take time to learn     | Slower delivery               | Keep MVP scope narrow and focus only on job search                              |
| Poor mapping design causes irrelevant results | Search quality drops          | Start with explicit field mapping and test iteratively                          |
| Seed data is unrealistic                      | Evaluation becomes misleading | Generate structured fake jobs with varied titles, skills, cities, salary ranges |
| Index sync becomes inconsistent               | Results become stale          | Implement reindex command and event-based partial sync                          |
| Overengineering ranking logic too early       | Delays MVP                    | Start with simple weighted relevance + freshness                                |

---

## 3. Learning Summary / Research Findings

## 3.1 Why Elasticsearch is a strong fit for a job platform

A job platform has both **text search** and **structured filtering** requirements. Traditional relational queries work for exact filters but become weak when users want to search naturally using queries like:

- `backend laravel remote`
- `react senior da nang`
- `java spring boot 2000 usd`

Elasticsearch is well suited because it provides:

- Full-text search with tokenization and scoring
- Fast faceted filtering across large datasets
- Custom ranking and weighted fields
- Typo tolerance and analyzers
- Aggregations for filter counts
- Good extensibility for recommendation and analytics use cases

## 3.2 Key insights for the job search domain

### Search is not just keyword matching

Users usually expect search to understand intent, not exact string matches. For job search, relevance often comes from a combination of:

- Job title match
- Skill match
- Location fit
- Salary fit
- Freshness / recency
- Company quality or popularity

This means the system should combine **text relevance** with **business ranking rules**.

### Structured fields matter as much as text fields

A high-quality search design should separate:

- searchable text fields
- exact filter fields
- sortable numeric fields
- facetable keyword fields

Example:

- `title`: text with analyzer
- `title.keyword`: exact value
- `skills`: keyword array
- `salary_min`, `salary_max`: integer
- `published_at`: date
- `city.slug`: keyword

### Denormalized search documents are necessary

Although the core system is relational, the search index should denormalize enough job data into one document so search queries stay fast and do not require join-like behavior at query time.

### Relevance tuning is iterative

There is no perfect scoring formula from day one. Relevance should be tuned based on:

- expected user behavior
- real search examples
- click-through and apply-through rates
- false positives and false negatives observed during testing

## 3.3 Recommended search document structure

A good Elasticsearch job document should contain:

```json
{
    "id": 123,
    "title": "Senior Backend Engineer",
    "title_suggest": "Senior Backend Engineer",
    "description": "Build scalable APIs using Laravel and MySQL...",
    "company": {
        "id": 10,
        "name": "Acme Tech",
        "slug": "acme-tech"
    },
    "skills": ["laravel", "php", "mysql", "redis"],
    "locations": ["ho-chi-minh", "da-nang"],
    "salary_min": 1500,
    "salary_max": 2500,
    "currency": "USD",
    "employment_type": "full-time",
    "work_model": "hybrid",
    "seniority": "senior",
    "published_at": "2026-04-01T09:00:00Z",
    "is_active": true
}
```

## 3.4 Recommended relevance model for MVP

For the MVP, ranking should remain explainable and simple.

Suggested scoring priorities:

1. Exact or near-exact title match
2. Skill match
3. Description match
4. Freshness boost
5. Optional salary visibility boost

A practical first version can use:

- `multi_match` across `title`, `skills_text`, `description`
- higher boost on `title`
- medium boost on `skills`
- lower boost on `description`
- `function_score` with decay on `published_at`

## 3.5 Recommended filters for MVP

The first search page should support:

- Keyword
- Skill
- Location
- Salary range
- Work model: remote/hybrid/office
- Seniority
- Employment type

These cover the most common candidate search behaviors while remaining manageable.

## 3.6 Recommended sort modes

To reduce complexity, support only a few sorts initially:

- Best match (default)
- Newest
- Salary high to low
- Salary low to high

## 3.7 Integration lessons for Laravel ecosystem

In a Laravel stack, Elasticsearch can be integrated cleanly if responsibilities are separated:

- Eloquent models remain source of truth
- Elasticsearch index acts as search-optimized read model
- Reindex command handles rebuilds
- Model observers or queue jobs handle incremental sync
- Search service class encapsulates query building

This avoids leaking Elasticsearch complexity into controllers and UI code.

## 3.8 Frontend lessons for React + Inertia

The search UI should feel instant and filter-oriented. Good UX patterns include:

- Debounced keyword input
- Clear filter chips
- Result count
- Empty state suggestions
- Loading skeletons
- URL-synced filters for shareable search pages

## 3.9 Infrastructure lessons

Even in Docker and local development, teams should think like production:

- Use explicit index names and aliases
- Plan for bulk indexing
- Log search requests and failures
- Make reindexing repeatable
- Keep mapping versioned in code

---

## 4. Product Requirement Document (PRD)

> **Note:** This section is the original draft PRD. The canonical, up-to-date version is [`docs/prd.md`](prd.md). Refer to that file for current phase definitions, scope, and requirements.

## 4.1 Product Title

Elasticsearch-powered Job Search MVP

## 4.2 Product Vision

Provide candidates with a fast, relevant, and scalable job search experience that significantly improves discoverability over traditional database search, while laying the technical foundation for advanced recommendation and talent matching capabilities.

## 4.3 Problem Statement

The current or baseline search approach in many Laravel applications often depends on SQL `LIKE`, simple filters, or underpowered full-text search. This leads to several product problems:

- Poor relevance for real-world queries
- Weak support for multi-field text search
- Limited ranking control
- Slow performance at higher scale
- Difficult extension into recommendation features

For a job platform, these limitations directly reduce user satisfaction, search success, and job application conversion.

## 4.4 Objectives

### Primary objective

Build a production-oriented MVP that enables keyword + filter-based job search using Elasticsearch.

### Secondary objectives

- Improve relevance of job results
- Improve responsiveness under larger data volumes
- Create a maintainable search architecture in Laravel
- Prepare for future recommendation and matching features

## 4.5 Target Users

### Candidate / job seeker

Wants to find relevant jobs quickly based on title, skill, city, salary, and work preference.

### Internal product / engineering team

Needs a maintainable and measurable search foundation that can evolve over time.

## 4.6 User Stories

### Core search stories

- As a candidate, I want to type keywords and see matching jobs.
- As a candidate, I want to filter jobs by skill, location, and salary.
- As a candidate, I want the most relevant jobs to appear first.
- As a candidate, I want to sort jobs by newest or salary.
- As a candidate, I want search results to load quickly.

### Edge search stories

- As a candidate, I want results even if my query is not an exact title match.
- As a candidate, I want filters to persist when I refresh the page.
- As a candidate, I want to know when no jobs match and how to recover.

### Internal stories

- As an engineer, I want jobs to be reindexed safely.
- As an engineer, I want search mappings versioned in code.
- As a PM, I want to compare search performance and quality before and after Elasticsearch.

## 4.7 Functional Requirements

### FR1 — Keyword search

The system shall allow users to search jobs using free-text keywords.

### FR2 — Structured filtering

The system shall allow users to filter jobs by:

- skill
- location
- salary range
- work model
- seniority
- employment type

### FR3 — Ranking

The system shall rank jobs by a relevance-aware algorithm that combines text match and freshness.

### FR4 — Sorting

The system shall allow sorting by:

- best match
- newest
- salary ascending
- salary descending

### FR5 — Pagination

The system shall support paginated results.

### FR6 — Active jobs only

The system shall return only published and active jobs by default.

### FR7 — Reindexing

The system shall support full reindex of jobs from the database into Elasticsearch.

### FR8 — Incremental sync

The system shall support updating Elasticsearch documents when jobs are created or updated.

### FR9 — Search UI integration

The system shall expose results to a React/Inertia frontend.

### FR10 — URL state

The search UI should persist keyword and filters in the URL.

## 4.8 Non-functional Requirements

### Performance

- Search response should feel near real-time
- Typical requests should ideally stay under 300 ms in staging/local benchmark environments

### Scalability

- Design should support at least 10,000–50,000 job documents for MVP testing
- Architecture should be extensible to much larger scale later

### Reliability

- Reindexing must be repeatable
- Sync failures should be observable through logs

### Maintainability

- Search mapping should be version-controlled
- Query building logic should live in a dedicated service layer

### Security

- Search API should not expose inactive/private jobs
- Employer-only fields must not be indexed into public search documents

## 4.9 Assumptions

- Source of truth remains relational database
- Laravel is the orchestration layer
- Elasticsearch is only used for search/read optimization
- Docker environment is available for local development
- UI should remain simple and MVP-focused

## 4.10 Constraints

- MBO timeline is short
- Team is still learning Elasticsearch
- No advanced AI ranking in MVP
- No production traffic yet for realistic click-based learning

## 4.11 UX Requirements

### Search page components

- Keyword search input
- Filter sidebar or modal
- Active filter chips
- Sort dropdown
- Result list cards
- Pagination or load more
- Empty state
- Loading state

### Job card should show

- Job title
- Company name
- Skills
- Location
- Salary range
- Work model
- Published date

## 4.12 API Design (suggested)

### Search endpoint

`GET /api/jobs/search`

### Example query params

```text
q=laravel backend
skills[]=php
skills[]=mysql
location=da-nang
salary_min=1000
salary_max=3000
work_model=hybrid
seniority=senior
sort=best_match
page=1
per_page=20
```

### Example response

```json
{
    "data": [
        {
            "id": 1,
            "title": "Senior Laravel Backend Engineer",
            "company": "Acme Tech",
            "locations": ["Da Nang"],
            "skills": ["PHP", "Laravel", "MySQL"],
            "salary_min": 1500,
            "salary_max": 2500,
            "work_model": "hybrid",
            "published_at": "2026-04-01T09:00:00Z"
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 142,
        "sort": "best_match"
    }
}
```

## 4.13 Data Model Requirements

### Relational tables involved

- jobs
- companies
- skills
- job_skills
- locations

### Search index requirements

The search index should denormalize fields needed for:

- keyword search
- filtering
- sorting
- display card rendering

## 4.14 Ranking Requirements

The default ranking should:

- strongly prioritize title matches
- boost jobs with matching skills
- consider description match as secondary
- boost recently published jobs
- exclude expired or inactive jobs

## 4.15 Acceptance Criteria

A release is accepted if:

1. A seeded dataset can be indexed into Elasticsearch
2. Search endpoint returns relevant results for common job queries
3. Users can filter by core fields
4. Search results are measurably faster or more capable than SQL search
5. Frontend renders search and filter state correctly
6. Engineering documentation is complete enough for handover

## 4.16 Example Search Scenarios

### Scenario 1

Query: `laravel`
Expected: Laravel-related backend/fullstack jobs rank near the top.

### Scenario 2

Query: `react da nang`
Expected: React jobs in Da Nang rank above React jobs outside Da Nang.

### Scenario 3

Query: `java remote 2000`
Expected: Remote Java jobs in an approximate salary fit rank higher.

### Scenario 4

Query: skill filter `php` + location `ho-chi-minh`
Expected: Only matching jobs are returned.

---

## 5. Long-term MBO (Expansion Plan)

> **Note:** Long-term phases are now defined in [`docs/prd.md`](prd.md) (Phases 3–4) and [`docs/task.md`](task.md). This section retains the original expansion themes for reference.

## 5.1 Long-term Objective

Evolve the Elasticsearch foundation into a strategic search and discovery platform for both candidates and employers.

## 5.2 Product Expansion Themes

### Theme 1 — Better relevance and search intelligence

After MVP, improve ranking quality using:

- click-through data
- apply-through data
- synonym dictionaries
- typo tolerance improvements
- phrase matching
- query rewriting
- search suggestions and autocomplete

### Theme 2 — Personalized candidate experience

Use search and behavioral data to power:

- recommended jobs
- saved search alerts
- recently viewed jobs
- profile-based job ranking
- salary-aware ranking

### Theme 3 — Employer-side talent discovery

Extend the search platform to support:

- resume/candidate search
- candidate ranking by skill fit
- CV parsing and indexing
- employer-side filtering by experience, skill, location

### Theme 4 — Analytics and product optimization

Introduce measurement loops:

- search CTR
- search-to-apply conversion
- zero-result queries
- most-used filters
- low-quality query detection
- ranking experiments / A/B tests

## 5.3 Suggested Long-term Roadmap

### Phase 1 — Stabilize MVP

- Harden indexing pipeline
- Improve observability
- Add test coverage for search service
- Tune field weights and analyzers

### Phase 2 — Search quality improvements

- Add autocomplete
- Add synonym support
- Add typo tolerance tuning
- Add aggregations for facet counts
- Add highlighted matches in results

### Phase 3 — Personalization

- Track click and apply events
- Create candidate preference profile
- Re-rank search results per user profile
- Build saved searches and smart alerts

### Phase 4 — Candidate / CV search

- Index candidate profiles and parsed resumes
- Create employer-facing candidate discovery tools
- Add permission-based visibility and privacy rules

### Phase 5 — Recommendation and matching engine

- Use search features plus embeddings / ML ranking later
- Match jobs to candidates and candidates to jobs
- Build recruiter recommendation workflows

## 5.4 Long-term Technical Expansion

### Search architecture evolution

- Use index aliases for zero-downtime reindexing
- Split indices by entity type: jobs, companies, candidates
- Add queue-based indexing pipeline
- Add background sync and retry logic
- Add analytics event pipeline for ranking learning

### Infrastructure evolution

- Move from local Docker-only to managed or clustered deployment
- Add monitoring for Elasticsearch health and query latency
- Define backup and restore process
- Define shard strategy based on growth

### Application architecture evolution

- Keep core domain in Laravel monolith initially
- Introduce modular boundaries around search
- Separate search service only when complexity justifies it

## 5.5 Long-term Success Metrics

| Metric                     | MVP target                   | Long-term direction           |
| -------------------------- | ---------------------------- | ----------------------------- |
| Median search latency      | under 300 ms in test/staging | under 150–250 ms at scale     |
| Zero-result rate           | baseline measurement         | continuous reduction          |
| Search CTR                 | baseline measurement         | continuous improvement        |
| Search to apply conversion | baseline measurement         | continuous improvement        |
| Reindex reliability        | manual but stable            | automated and observable      |
| Supported entities         | jobs only                    | jobs + candidates + companies |

## 5.6 Recommended Engineering Practices for Long-term Success

- Version Elasticsearch mappings in code
- Keep search query builder isolated in service classes
- Use DTOs or transformers for index documents
- Add smoke tests for important queries
- Track search analytics from day one
- Document assumptions and scoring rules clearly
- Prefer simple explainable ranking before machine learning

---

## 6. Recommended Implementation Blueprint

## 6.1 High-level architecture

```text
[React + Inertia Search UI]
          |
          v
[Laravel REST API]
          |
          +----------------------+
          |                      |
          v                      v
 [Relational Database]   [Elasticsearch]
          |                      ^
          |                      |
          +--> [Indexing Jobs / Queues / Reindex Commands]
```

## 6.2 Suggested Laravel structure

```text
app/
  Domain/
    Job/
  Services/
    Search/
      JobSearchService.php
      JobIndexDocumentFactory.php
      JobSearchQueryBuilder.php
  Console/Commands/
    ReindexJobsCommand.php
  Jobs/
    SyncJobToElasticsearch.php
  Http/Controllers/Api/
    JobSearchController.php
```

## 6.3 Suggested React search page structure

```text
resources/js/Pages/Jobs/Search.tsx
resources/js/Components/Search/
  SearchBar.tsx
  SearchFilters.tsx
  SearchResults.tsx
  JobCard.tsx
  ActiveFilterChips.tsx
  SortSelect.tsx
```

---

## 7. Final Recommendation

The best MBO approach is to treat Elasticsearch not as a generic technology experiment, but as a **focused product capability project**.

The short-term win is a job search MVP that proves four things:

1. Elasticsearch integrates cleanly into the Laravel stack
2. Search quality is better than naive database search
3. The system can handle realistic job data volume
4. The architecture can evolve into recommendation and talent matching later

If implemented with a narrow scope, realistic data, and measurable evaluation criteria, this MBO will produce both practical engineering value and a strong foundation for future search-driven product capabilities.

---

## 8. Appendix: Suggested Demo Checklist

Before presenting the MBO result, ensure the demo can show:

- Search `laravel` and see ranked results
- Filter by skill, location, salary
- Compare SQL search vs Elasticsearch behavior
- Show reindex command working
- Show index mapping file
- Show Docker setup
- Show dataset volume and latency numbers
- Explain next steps for recommendation and candidate search
