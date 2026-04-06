# PRD — Larasearch Job Platform

> Version: 1.0  
> Goal: Build a production-ready job platform MVP that proves Elasticsearch value first, while keeping the data model extensible toward a fuller IT job marketplace.

## 1. Executive Summary

Larasearch is a smart job platform inspired by ITviec, LinkedIn Jobs, and VietnamWorks, but scoped for an MBO-friendly build path:

- **Short-term objective**: prove the value of Elasticsearch for job search using a realistic job marketplace domain.
- **Medium-term objective**: evolve from “search demo” into a real hiring platform with candidate, employer, application, and notification flows.
- **Long-term objective**: support recommendation, candidate sourcing, saved searches, company branding, and resume/job matching.

This PRD updates the earlier search-first scope into a **phase-based product roadmap** with a relational schema that supports:

- candidate and employer accounts
- companies and recruiter access
- job listings and rich filters
- applications and status tracking
- skills, categories, and locations
- saved jobs, alerts, notifications
- resume handling and future AI matching

## 2. Product Vision

### 2.1 Problem

Traditional SQL search makes it hard to deliver a modern job discovery experience with:

- full-text relevance across title, description, company, and skills
- faceted filtering
- autocomplete
- related jobs
- ranking by relevance + freshness + business signals

At the same time, a search demo without a solid domain model quickly hits a ceiling when adding real marketplace features such as applications, resumes, employer workflows, and notifications.

### 2.2 Vision

Build a **modular job platform** where:

- **MySQL** is the transactional source of truth
- **Elasticsearch** is the search/read model
- **Laravel** owns business workflows and consistency
- **React + Inertia** delivers a fast, search-heavy UI

### 2.3 Success Criteria

The product is successful when it can:

1. Search 5k+ jobs with low-latency faceted search
2. Support realistic core hiring workflows end-to-end
3. Keep schema and architecture extensible for future recommendation and CV matching
4. Demonstrate measurable search quality improvement over DB-only search

## 3. Product Scope by Phase

### Phase Definitions

- Phase 0: Search Core
- Milestone: Search MVP (built on Phase 0)
- Phase 1: Marketplace Core
- Phase 2: Search Analytics
- Phase 3: Resume & Matching Readiness

## Phase 0 — Search Core

### Objective

Set up local environment, schema, seed data, Elasticsearch connectivity, and architecture scaffolding.

### In Scope

- Docker services: app, DB, Redis, Elasticsearch, Kibana
- Base relational schema
- Seeders/factories for realistic job data
- Elasticsearch client setup
- Index bootstrap and bulk indexing
- Search service abstractions
- Queue-ready sync pipeline scaffolding

### Out of Scope

- Candidate application flows
- Employer portal UI
- Resume parsing
- Recommendation engine

### Deliverables

- bootable local stack
- relational schema + migrations
- 5k+ seeded jobs
- initial ES index + alias
- health check + indexing commands

## Search MVP Milestone (MBO Focus)

### Objective

Prove Elasticsearch value through a real job-search experience.

### Primary Users

- authenticated candidate searching jobs
- internal evaluator benchmarking relevance and speed

Search MVP is authenticated-only. Public visitor job browsing is out of scope for this milestone.

### In Scope

- keyword search
- filters
- facets / aggregations
- sorting
- pagination via app-owned normalized results
- result payloads with pagination metadata
- autocomplete
- highlighting
- related jobs
- benchmark comparison: Elasticsearch vs MySQL baseline (optional)
- job detail page
- company snippets on job pages

### Out of Scope

- full employer portal
- full application tracking dashboard
- advanced personalization
- vector search / ML ranking

### MVP Features

1. Search page
2. Search suggestions
3. Job detail page
4. Related jobs
5. Search analytics baseline
6. Async DB → ES sync
7. Benchmark report
8. Normalized search result contract consumed independently of Elasticsearch internals

### Key Results

- p50 search latency < 150ms on seeded dataset
- p95 search latency < 350ms on main query set
- autocomplete p95 < 120ms
- Precision@5 / Hit@10 shows visible improvement over DB search
- index sync works for create/update/delete flows
- search responses include stable pagination metadata without exposing raw Elasticsearch hits

### Search Implementation Rules

- MySQL is the transactional source of truth.
- Elasticsearch is the user-facing search backend.
- `DatabaseSearchService` exists only for benchmark comparison and does not define product behavior.
- The canonical request and response contract is defined in [`reference.md`](reference.md).
- Search consumers must not depend on raw Elasticsearch payloads such as `hits`, `_source`, or highlight arrays.

## Phase 1 — Marketplace Core

### Objective

Turn the search MVP into a usable hiring platform.

### Primary Users

- candidate
- recruiter / employer
- admin / operations

### In Scope

- candidate accounts and profiles
- employer accounts and company membership
- applications
- application status tracking
- saved jobs
- followed companies
- notifications (via Laravel built-in notifications)
- company pages
- basic company reviews

### Out of Scope

- AI screening
- semantic matching
- recruiter CRM automation
- billing/payments

## Phase 2 — Search Analytics

### Objective

Strengthen retention, discovery, and employer value through analytics and saved-search workflows.

### In Scope

- saved searches and email alerts
- search analytics (query tracking, impressions, clicks)
- zero-result query tracking
- recommendation prep (rule-based "jobs for you")
- synonym management and relevance tuning
- performance and operations (alias-based reindexing, caching, monitoring)

### Out of Scope

- AI/ML-based matching
- vector/semantic search

## Phase 3 — Resume & Matching Readiness

### Objective

Prepare the platform for recommendation and sourcing.

### In Scope

- resume storage and parsing-ready schema
- candidate skills and experience graph
- recommendation scaffolding
- employer-facing candidate discovery architecture
- search analytics and relevance tuning
- semantic/vector search exploration

### Potential Features

- “Jobs for you”
- “Candidates for this job”
- AI Match-style shortlist generation
- salary intelligence
- recruiter outreach workflow

## 4. Actors and Permissions

| Actor          | Description                | Core Permissions                                          |
| -------------- | -------------------------- | --------------------------------------------------------- |
| Visitor        | Unauthenticated visitor    | View marketing and authentication entry points            |
| Candidate      | Authenticated job seeker   | Save jobs, upload resume, apply, manage profile, alerts   |
| Employer User  | Recruiter/hiring manager   | Manage company jobs, review applications, update statuses |
| Company Admin  | Employer with admin rights | Manage company members, branding, jobs, workflows         |
| Platform Admin | Internal operations/admin  | Moderate companies, reviews, jobs, users, analytics       |

## 5. Core User Flows

### 5.1 Candidate Job Search Flow

1. User signs in and lands on search page
2. Enters keyword and applies filters
3. Reviews results with highlighted snippets and facets
4. Opens job detail
5. Saves job or applies
6. Optionally creates alert for similar searches

### 5.2 Candidate Apply Flow

1. Candidate signs in
2. Opens job detail
3. Chooses resume / uploads new resume
4. Adds optional cover note
5. Submits application
6. Receives confirmation notification
7. Tracks status changes later

### 5.3 Employer Job Management Flow

1. Employer signs in under company account
2. Creates or edits job post
3. Publishes job
4. Job syncs to Elasticsearch
5. Receives applications
6. Moves applications through statuses

### 5.4 Saved Search Alert Flow

1. Candidate searches with filters
2. Clicks save search
3. Chooses frequency
4. System stores filter JSON
5. Background job sends alerts for new matching jobs

## 6. Functional Requirements

### 6.1 Authentication & User Management

- email/password auth
- role-aware authorization
- candidate and employer profile support
- company membership model
- status flags: active, suspended, pending

### 6.2 Job Posting & Management

- job create/edit/publish/expire
- salary, work mode, level, location, category, skills
- featured job flag
- active/inactive state
- publish timestamps and expiry

### 6.3 Job Search & Discovery

- keyword search across title, description, company, skills
- faceted filters
- sorting by relevance, newest, salary
- related jobs
- highlighted snippets
- search suggestions

### 6.4 Applications

- one candidate can apply to many jobs
- one job can have many applications
- application status history must be preserved
- duplicate apply rules configurable

### 6.5 Resume / Candidate Profile

- resume file storage
- default resume selection
- visibility rules
- parsed data storage reserved for future parsing pipeline
- candidate skills and preferences

### 6.6 Company Profiles

- public company page
- overview, website, branding, size, industry
- job listings by company
- optional reviews and awards

### 6.7 Notifications

- application submitted
- application status changed
- saved-search alerts
- recruiter actions
- system messages

### 6.8 Search Analytics

- query tracking
- click-through tracking
- zero-result queries
- benchmark reporting

## 7. Non-Functional Requirements

### Performance

- low-latency search and autocomplete
- queue-based indexing and notifications
- scalable seed volume for benchmarking

### Reliability

- idempotent indexing jobs
- retries for async sync
- alias-based reindexing support

### Security

- hashed passwords
- secure file storage for resumes
- authorization boundaries between candidate/employer/admin
- rate limiting for auth, apply, and search abuse

### SEO

- searchable job listing URLs
- job detail pages crawlable
- company pages crawlable
- SSR-friendly via Laravel + Inertia rendering

## 8. Domain Model Decisions

### Why `job_listings` instead of `jobs`

Laravel already uses `jobs` for queue storage in many projects. Use `job_listings` to avoid collisions and confusion.

### Why phase the schema

The original search MVP schema is not enough for:

- applications
- resumes
- notifications
- company memberships
- future recommendations

So the schema is split into phases:

- **Phase 0**: tables required to seed and search jobs
- **Search MVP milestone**: search UI and Elasticsearch search capabilities on top of Phase 0
- **Phase 1**: candidate, employer, and application workflows
- **Phase 2**: search analytics, saved searches, and growth features
- **Phase 3**: resume and recommendation readiness

## 9. KPIs

| KPI                     | Target                        |
| ----------------------- | ----------------------------- |
| Search p50              | < 150ms                       |
| Search p95              | < 350ms                       |
| Autocomplete p95        | < 120ms                       |
| Indexing throughput     | measurable and reported       |
| Precision@5             | better than DB baseline       |
| Zero-result rate        | tracked and reduced over time |
| Apply conversion        | tracked in later phases       |
| Saved-search activation | tracked in Phase 2            |

## 10. Risks and Mitigations

| Risk                        | Impact                      | Mitigation                                  |
| --------------------------- | --------------------------- | ------------------------------------------- |
| Mapping lock-in             | Expensive reindexing        | Use versioned indices + aliases             |
| Schema too narrow           | Blocks future features      | Add phased marketplace schema now           |
| Over-engineering early      | Slows MBO delivery          | Keep Search MVP milestone tightly scoped    |
| Relevance tuning complexity | Weak demo quality           | Define benchmark query set early            |
| Queue/index drift           | Inconsistent search results | Use after-commit dispatch + re-sync command |
| Resume/PII security gaps    | High trust risk             | Private object storage + signed URLs        |

## 11. Release Strategy

### Release 1

- Search MVP demo with benchmark report

### Release 2

- Candidate accounts, applications, company pages, saved jobs

### Release 3

- Notifications, alerts, reviews, employer workflows

### Release 4

- Resume intelligence, recommendations, advanced ranking

## 12. Appendix — Deliverable Files

This PRD assumes companion documentation files:

- `schema.md` — phased relational schema
- `erd.md` — text-based ERD and relationships
- `task.md` — execution plan and checklist aligned to phases
