# MBO Project Document: Elasticsearch for Job Platform

## 1. Executive Summary

This document defines a practical Management by Objectives (MBO) plan to research, validate, and implement Elasticsearch for a job platform built with Laravel, InertiaJS, ReactJS, TailwindCSS, shadcn/ui, Docker, and MySQL.

The short-term goal is to deliver a working MVP focused on **job search** with meaningful improvements in search relevance, speed, filterability, and extensibility. The long-term goal is to evolve the search foundation into an intelligent talent platform capable of powering recommendations, CV-to-job matching, employer-side candidate discovery, analytics, and ranking optimization.

---

## Companion Documentation

| File | Description |
|------|-------------|
| [prd.md](prd.md) | Product Requirements Document — canonical phase definitions, scope, user flows, functional/non-functional requirements, KPIs, and release strategy |
| [erd.md](erd.md) | Entity-Relationship Diagram — text-based domain model with phase-based relationship maps and cardinality summary |
| [schema.md](schema.md) | Database Schema — phased relational table definitions, column specs, indexes, and implementation notes |
| [task.md](task.md) | Task Plan — phase-aligned execution checklist with milestone exit criteria and definition of done |
| [reference.md](reference.md) | Technical Reference — current Laravel search structure, Elasticsearch document/mapping contract, command workflow, alias strategy, and verification flow |

---

## 2. Short-term MBO (2–4 weeks)

## 2.1 Objective

Build and validate an Elasticsearch-based job search MVP that supports keyword search, structured filters, and ranking logic suitable for a production-oriented job platform.

## 2.2 Business Goal

Enable users to find relevant jobs faster and more accurately than with database `LIKE` queries or naive filtering, while creating a scalable foundation for future search and recommendation features.

## 2.3 Scope of MVP

The MVP will focus only on the core authenticated candidate search journey:

- Search jobs by keyword
- Filter by skill, location, salary range
- Sort and rank by relevance and freshness
- Sync job data from Laravel application database to Elasticsearch
- Expose normalized search results through the app-owned API contract
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

- Authenticated users can search jobs by free-text keyword
- Users can filter by skill, city/location, and salary range
- Search results are ranked in a way that feels better than plain database search
- Search API can support pagination and sorting through normalized result payloads with pagination metadata

### Technical success

- Elasticsearch runs locally through Docker
- Laravel can index and re-index jobs reliably
- Search endpoint response time is consistently low under test dataset volume
- Search mapping supports future extension

### Measurement success

- Search latency for common queries is acceptable, ideally under 150–300 ms in local/staging conditions
- Test data volume reaches at least 5,000 jobs, with larger datasets used later as needed
- Team documents relevance findings and trade-offs

## 2.5 Deliverables

By the end of the short-term MBO, the following deliverables should exist:

1. Dockerized Elasticsearch setup
2. Laravel indexing pipeline for jobs
3. Job search flow backed by Elasticsearch
4. React/Inertia search UI with filters
5. Seed dataset with realistic fake jobs
6. Index mapping and query design documentation
7. Relevance testing notes
8. Search performance summary
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
- Review search quality against expected relevance outcomes
- Write learning summary and next-phase recommendations

## 2.7 Key Risks and Mitigations

| Risk                                          | Impact                        | Mitigation                                                                      |
| --------------------------------------------- | ----------------------------- | ------------------------------------------------------------------------------- |
| Elasticsearch concepts take time to learn     | Slower delivery               | Keep MVP scope narrow and focus only on job search                              |
| Poor mapping design causes irrelevant results | Search quality drops          | Start with explicit field mapping and test iteratively                          |
| Seed data is unrealistic                      | Evaluation becomes misleading | Generate structured fake jobs with varied titles, skills, cities, salary ranges |
| Index sync becomes inconsistent               | Results become stale          | Implement reindex command and event-based partial sync                          |
| Overengineering ranking logic too early       | Delays MVP                    | Start with simple weighted relevance + freshness                                |

Search responses should stay provider-agnostic: Elasticsearch is a read backend, while controllers and UI consume a normalized job-result contract owned by the application.

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
- `locations`: keyword

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
    "description": "Build scalable APIs using Laravel and MySQL...",
    "company_name": "Acme Tech",
    "company_slug": "acme-tech",
    "skills": ["Laravel", "PHP", "MySQL", "Redis"],
    "skills_text": "Laravel PHP MySQL Redis",
    "locations": ["ho-chi-minh", "da-nang"],
    "location_labels": ["Ho Chi Minh City", "Da Nang"],
    "category_names": ["Backend"],
    "salary_min": 1500,
    "salary_max": 2500,
    "salary_currency": "USD",
    "salary_is_visible": true,
    "job_type": "full-time",
    "work_model": "hybrid",
    "experience_level": "senior",
    "published_at": "2026-04-01T09:00:00Z",
    "expires_at": null,
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
- Work model: remote/hybrid/onsite
- Experience level
- Job type

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

## 4. Documentation Boundaries

This file is now an overview and rationale document only.

- Product scope, users, release planning, and phase definitions are canonical in [`prd.md`](prd.md).
- Relational tables and field names are canonical in [`schema.md`](schema.md).
- Search contract, Elasticsearch document shape, and service responsibilities are canonical in [`reference.md`](reference.md).
- Phase-by-phase execution status is canonical in [`task.md`](task.md).
- Long-term implementation plans remain in [`docs/superpowers/plans/2026-04-05-phase-0-foundation-search-domain.md`](superpowers/plans/2026-04-05-phase-0-foundation-search-domain.md).

When this document mentions search behavior, it is explanatory only. The canonical names and payloads are owned by the companion docs above.
