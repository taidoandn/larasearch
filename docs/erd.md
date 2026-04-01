# ERD (SEARCH MVP) (Short-term)

companies (1) --- (N) job_listings
job_listings (N) --- (N) skills (via job_listing_skill)
job_listings (N) --- (N) categories (via category_job_listing)
job_listings (N) --- (1) locations

skills (1) --- (N) job_listing_skill
categories (1) --- (N) category_job_listing

Optional:
search_queries --- job_impressions --- job_listings
search_queries --- job_clicks --- job_listings

# ERD — Text-based Domain Model (Long-term)

## 1. High-level ERD

```text
companies (1) ────────< job_listings >──────── (1) locations
    │                         │
    │                         ├────────< category_job_listing >──────── categories
    │                         │
    │                         ├────────< job_listing_skill >─────────── skills
    │                         │
    │                         ├────────< applications >─────────────── users
    │                         │                     │
    │                         │                     └──────< application_status_histories
    │                         │
    │                         ├────────< job_impressions
    │                         ├────────< job_clicks
    │                         └────────< search_queries (optional clicked job)
    │
    ├────────< company_users >──────── users
    │
    ├────────< company_reviews >────── users
    │
    ├────────< followed_companies >─── users
    │
    └────────< candidate_contact_requests >──── users (candidate)
                                             └── users (requested_by recruiter)

users (1) ──────── (0..1) candidate_profiles
  │
  ├────────< user_skills >──────────── skills
  ├────────< saved_jobs >───────────── job_listings
  ├────────< notifications
  ├────────< saved_searches
  ├────────< resumes
  └────────(0..1) candidate_search_profiles
```

## 2. Phase-based ERD

## Phase 0 — Search Core

```text
companies (1) ────────< job_listings >──────── (0..1) locations
job_listings >────────< category_job_listing >──────── categories
job_listings >────────< job_listing_skill >─────────── skills
categories (1) ───────< categories.parent_id (self reference)
```

### Meaning

- One company has many job listings.
- One job listing belongs to one primary location initially.
- One job listing can have many categories.
- One job listing can have many skills.

## Phase 1 — Search Analytics

```text
users (0..1) ────────< saved_searches
users (0..1) ────────< search_queries
search_queries (0..1) ────────< job_impressions >──────── job_listings
search_queries (0..1) ────────< job_clicks >───────────── job_listings
search_queries (0..1) ──────── clicked_job_listing_id ───> job_listings
```

## Phase 2 — Marketplace Core

```text
users (1) ──────── (0..1) candidate_profiles
users >────────< user_skills >──────── skills

companies (1) ────────< company_users >──────── users
users >────────< saved_jobs >────────────────── job_listings
users >────────< followed_companies >────────── companies

job_listings (1) ────────< applications >────── users
applications (1) ────────< application_status_histories
users (1) ────────< notifications
companies (1) ────────< company_reviews >────── users
```

## Phase 3 — Resume & Matching Readiness

```text
users (1) ────────< resumes
users (1) ──────── (0..1) candidate_search_profiles

users (candidate) (1) ────────< candidate_contact_requests >──────── companies
users (recruiter) (1) ────────┘
candidate_contact_requests (0..1) ────────> job_listings
applications (0..N) ────────> resumes
```

## 3. Cardinality Summary

| Relationship                                | Type                   |
| ------------------------------------------- | ---------------------- |
| company -> job_listings                     | one-to-many            |
| location -> job_listings                    | one-to-many            |
| job_listings -> skills                      | many-to-many           |
| job_listings -> categories                  | many-to-many           |
| user -> candidate_profile                   | one-to-one             |
| company -> company_users                    | one-to-many            |
| user -> company_users                       | one-to-many            |
| user -> applications                        | one-to-many            |
| job_listing -> applications                 | one-to-many            |
| application -> application_status_histories | one-to-many            |
| user -> saved_jobs                          | many-to-many via pivot |
| user -> followed_companies                  | many-to-many via pivot |
| user -> user_skills                         | many-to-many via pivot |
| company -> reviews                          | one-to-many            |
| user -> notifications                       | one-to-many            |
| user -> resumes                             | one-to-many            |

## 4. Search Read Model vs Relational Model

```text
MySQL relational model
  companies
  locations
  categories
  skills
  job_listings
  pivots
      │
      │ async sync / bulk indexing
      ▼
Elasticsearch read model
  job_listings_current
    - company_name
    - location_display
    - category_names
    - skill_names
    - salary fields
    - ranking signals
    - searchable text
```

## 5. Suggested Future Extensions

```text
job_listings >────────< job_listing_locations >──────── locations
companies ────────< company_awards
users ────────< user_experiences
users ────────< user_educations
job_listings ────────< recruiter_notes
```
