# ERD — Current and Future Domain Model

> If you are implementing the current MVP, read the Search MVP and Phase 0 sections first. The later sections describe future phases and extensions.

## Search MVP (Current)

companies (1) --- (N) job_listings
job_listings (N) --- (N) skills (via job_listing_skill)
job_listings (N) --- (N) categories (via category_job_listing)
job_listings (N) --- (1) locations

skills (1) --- (N) job_listing_skill
categories (1) --- (N) category_job_listing

Optional:
search_queries --- job_impressions --- job_listings
search_queries --- job_clicks --- job_listings
search_queries.clicked_job_listing_id --- job_listings (derived shortcut, optional)

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

### Phase Definitions

- Phase 0: Search Core
- Phase 1: Marketplace Core
- Phase 2: Search Analytics
- Phase 3: Resume & Matching Readiness

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

## Phase 1 — Marketplace Core

```text
users (1) ──────── (0..1) candidate_profiles
users >────────< user_skills >──────── skills

companies (1) ────────< company_users >──────── users
users >────────< saved_jobs >────────────────── job_listings
users >────────< followed_companies >────────── companies

job_listings (1) ────────< applications >────── users
applications (1) ────────< application_status_histories
users (1) ────────< notifications (Laravel built-in)
companies (1) ────────< company_reviews >────── users
```

## Phase 2 — Search Analytics

```text
users (0..1) ────────< saved_searches
users (0..1) ────────< search_queries
search_queries (0..1) ────────< job_impressions >──────── job_listings
search_queries (0..1) ────────< job_clicks >───────────── job_listings
search_queries (0..1) ──────── clicked_job_listing_id ───> job_listings
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

## 4. Event Model Notes

- `search_queries`, `job_impressions`, and `job_clicks` are analytics tables and should still carry both `created_at` and `updated_at` for consistency with the rest of the schema.
- Anonymous analytics should still retain attribution via `session_id` when `user_id` is null.
- `search_queries.clicked_job_listing_id` is a denormalized convenience field and should represent the latest clicked job for that query if the application keeps it.

## 5. Search Read Model vs Relational Model

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
    - company_slug
    - locations
    - location_labels
    - category_names
    - skills
    - skills_text
    - job_type
    - work_model
    - experience_level
    - salary fields
    - ranking signals
    - searchable text
```

## 6. Suggested Future Extensions

```text
job_listings >────────< job_listing_locations >──────── locations
companies ────────< company_awards
users ────────< user_experiences
users ────────< user_educations
job_listings ────────< recruiter_notes
```
