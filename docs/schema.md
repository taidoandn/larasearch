# Schema Design — Phased Job Platform

> This schema is intentionally split by phase so implementation can stay aligned with delivery priorities.

## Naming Convention

- Use `job_listings` instead of `jobs` to avoid conflict with Laravel queue tables.
- Use bigint unsigned primary keys.
- Add `created_at` and `updated_at` to every documented table.
- Always list `created_at` and `updated_at` as the final two rows in each table definition.
- Use soft deletes only where business recovery is useful; otherwise keep explicit `status` fields.

## Phase Definitions

- Phase 0: Search Core
- Phase 1: Marketplace Core
- Phase 2: Search Analytics
- Phase 3: Resume & Matching Readiness

## Cross-cutting Rules

- Framework-owned tables may be exceptions to the bigint PK convention. Laravel's `notifications` table is one such exception.
- Every documented table should include both `created_at` and `updated_at`, including pivots and analytics tables.
- Analytics tables must record at least one actor key: `user_id` or `session_id`.
- Denormalized helper fields must declare their source of truth. `search_queries.clicked_job_listing_id` is derived from click activity and should represent the latest clicked job for that query.
- Foreign key delete behavior must be declared in migrations. Use `cascadeOnDelete()` for hard-dependent pivots, `nullOnDelete()` for optional references, and `restrictOnDelete()` or soft-delete parents for historical records that should survive.
- Enum storage is a project-level convention, not a per-table preference. Future schema changes should follow the same rule unless the schema docs are explicitly updated.
- Use `varchar` plus PHP string-backed enums for every enum-like application-owned column, including statuses, roles, types, states, filters, labels, preferences, and source-like values.
- Do not use database enums for application-owned columns in this schema.

# Phase 0 — Core Search Domain

## `companies`

| Field        | Type                         | Notes                     |
| ------------ | ---------------------------- | ------------------------- |
| id           | bigint PK                    |                           |
| slug         | varchar(160) unique          | SEO/public URL            |
| name         | varchar(160)                 |                           |
| legal_name   | varchar(200) nullable        |                           |
| description  | text nullable                | public overview           |
| website_url  | varchar(255) nullable        |                           |
| logo_url     | varchar(255) nullable        |                           |
| industry     | varchar(120) nullable        |                           |
| company_size | varchar(50) nullable         | example: 11-50            |
| founded_year | smallint nullable            |                           |
| country_code | char(2) nullable             | ISO country               |
| is_verified  | boolean default false        |                           |
| status       | varchar(30) default 'active' | PHP string-backed enum: active, hidden, suspended |
| created_at   | timestamp                    |                           |
| updated_at   | timestamp                    |                           |

**Indexes**

- unique(slug)
- index(name)
- index(status)

## `categories`

| Field     | Type                                | Notes                 |
| --------- | ----------------------------------- | --------------------- |
| id        | bigint PK                           |                       |
| parent_id | bigint FK nullable -> categories.id | hierarchical taxonomy |
| slug      | varchar(120) unique                 |                       |
| name      | varchar(120)                        |                       |
| is_active | boolean default true                |                       |
| created_at | timestamp                          |                       |
| updated_at | timestamp                          |                       |

**Indexes**

- unique(slug)
- index(parent_id)
- index(is_active)

## `skills`

| Field        | Type                 | Notes                     |
| ------------ | -------------------- | ------------------------- |
| id           | bigint PK            |                           |
| slug         | varchar(120) unique  |                           |
| name         | varchar(120) unique  |                           |
| category     | varchar(80) nullable | backend, frontend, devops |
| aliases_json | json nullable        | future synonyms           |
| created_at   | timestamp            |                           |
| updated_at   | timestamp            |                           |

**Indexes**

- unique(slug)
- unique(name)

## `locations`

| Field         | Type                   | Notes      |
| ------------- | ---------------------- | ---------- |
| id            | bigint PK              |            |
| country_code  | char(2)                |            |
| state_name    | varchar(120) nullable  |            |
| city_name     | varchar(120)           |            |
| district_name | varchar(120) nullable  |            |
| display_name  | varchar(180)           |            |
| latitude      | decimal(10,7) nullable | future geo |
| longitude     | decimal(10,7) nullable | future geo |
| is_active     | boolean default true   |            |
| created_at    | timestamp              |            |
| updated_at    | timestamp              |            |

**Indexes**

- index(country_code, city_name)
- index(is_active)

## `job_listings`

| Field               | Type                               | Notes                           |
| ------------------- | ---------------------------------- | ------------------------------- |
| id                  | bigint PK                          |                                 |
| company_id          | bigint FK -> companies.id          |                                 |
| primary_location_id | bigint FK nullable -> locations.id |                                 |
| slug                | varchar(180) unique                |                                 |
| title               | varchar(180)                       |                                 |
| normalized_title    | varchar(180) nullable              |                                 |
| short_description   | text nullable                      | teaser/snippet                  |
| description         | longtext                           |                                 |
| requirements        | longtext nullable                  |                                 |
| benefits            | longtext nullable                  |                                 |
| job_type            | varchar(30)                        | full-time, contract, internship |
| work_model          | varchar(20)                        | onsite, hybrid, remote          |
| experience_level    | varchar(20)                        | entry, mid, senior, lead        |
| salary_min          | integer nullable                   |                                 |
| salary_max          | integer nullable                   |                                 |
| salary_currency     | char(3) nullable                   | VND, USD                        |
| salary_is_visible   | boolean default true               |                                 |
| application_url     | varchar(255) nullable              | external apply URL              |
| is_featured         | boolean default false              |                                 |
| is_active           | boolean default true               |                                 |
| source_type         | varchar(30) default 'direct'       | PHP string-backed enum: direct, imported |
| published_at        | timestamp nullable                 |                                 |
| expires_at          | timestamp nullable                 |                                 |
| created_at          | timestamp                          |                                 |
| updated_at          | timestamp                          |                                 |

**Indexes**

- unique(slug)
- index(company_id)
- index(primary_location_id)
- index(job_type)
- index(work_model)
- index(experience_level)
- index(is_active, published_at)
- index(expires_at)

## `category_job_listing`

| Field          | Type                         | Notes |
| -------------- | ---------------------------- | ----- |
| category_id    | bigint FK -> categories.id   |       |
| job_listing_id | bigint FK -> job_listings.id |       |
| created_at     | timestamp                    |       |
| updated_at     | timestamp                    |       |

**Primary Key**

- composite PK(category_id, job_listing_id)

**Indexes**

- index(job_listing_id)

## `job_listing_skill`

| Field          | Type                         | Notes                       |
| -------------- | ---------------------------- | --------------------------- |
| job_listing_id | bigint FK -> job_listings.id |                             |
| skill_id       | bigint FK -> skills.id       |                             |
| is_primary     | boolean default false        |                             |
| weight         | smallint default 1           | higher means more important |
| created_at     | timestamp                    |                             |
| updated_at     | timestamp                    |                             |

**Primary Key**

- composite PK(job_listing_id, skill_id)

**Indexes**

- index(skill_id)
- index(job_listing_id, weight)

# Phase 1 — User, Employer, and Application Domain

## `users`

| Field             | Type                         | Notes                      |
| ----------------- | ---------------------------- | -------------------------- |
| id                | bigint PK                    |                            |
| email             | varchar(190) unique          |                            |
| password          | varchar(255)                 | Laravel default column     |
| full_name         | varchar(160)                 |                            |
| phone             | varchar(40) nullable         |                            |
| avatar_url        | varchar(255) nullable        |                            |
| role              | varchar(20)                  | PHP string-backed enum: candidate, employer, admin |
| status            | varchar(20) default 'active' | PHP string-backed enum: active, suspended, pending |
| email_verified_at | timestamp nullable           |                            |
| last_login_at     | timestamp nullable           |                            |
| created_at        | timestamp                    |                            |
| updated_at        | timestamp                    |                            |

**Indexes**

- unique(email)
- index(role, status)

## `candidate_profiles`

| Field                   | Type                               | Notes |
| ----------------------- | ---------------------------------- | ----- |
| user_id                 | bigint PK/FK -> users.id           | 1:1   |
| headline                | varchar(180) nullable              |       |
| summary                 | text nullable                      |       |
| years_of_experience     | decimal(4,1) nullable              |       |
| current_location_id     | bigint FK nullable -> locations.id |       |
| expected_salary_min     | integer nullable                   |       |
| expected_salary_max     | integer nullable                   |       |
| salary_currency         | char(3) nullable                   |       |
| preferred_work_model    | varchar(20) nullable               |       |
| open_to_work            | boolean default false              |       |
| searchable_by_employers | boolean default false              |       |
| created_at              | timestamp                          |       |
| updated_at              | timestamp                          |       |

**Indexes**

- index(current_location_id)
- index(open_to_work, searchable_by_employers)

## `company_users`

| Field        | Type                      | Notes                   |
| ------------ | ------------------------- | ----------------------- |
| id           | bigint PK                 |                         |
| company_id   | bigint FK -> companies.id |                         |
| user_id      | bigint FK -> users.id     |                         |
| company_role | varchar(20)               | PHP string-backed enum: owner, admin, recruiter |
| is_active    | boolean default true      |                         |
| created_at   | timestamp                 |                         |
| updated_at   | timestamp                 |                         |

**Indexes**

- unique(company_id, user_id)
- index(user_id)
- index(company_role)

## `user_skills`

| Field             | Type                   | Notes                            |
| ----------------- | ---------------------- | -------------------------------- |
| user_id           | bigint FK -> users.id  |                                  |
| skill_id          | bigint FK -> skills.id |                                  |
| proficiency_level | varchar(20) nullable   | beginner, intermediate, advanced |
| years_experience  | decimal(4,1) nullable  |                                  |
| last_used_year    | smallint nullable      |                                  |
| created_at        | timestamp              |                                  |
| updated_at        | timestamp              |                                  |

**Primary Key**

- composite PK(user_id, skill_id)

**Indexes**

- index(skill_id)

## `applications`

| Field                  | Type                             | Notes |
| ---------------------- | -------------------------------- | ----- |
| id                     | bigint PK                        |       |
| job_listing_id         | bigint FK -> job_listings.id     |       |
| user_id                | bigint FK -> users.id            | candidate |
| resume_id              | bigint FK nullable -> resumes.id | added in Phase 3 |
| cover_letter           | text nullable                    |       |
| source                 | varchar(30) default 'site_apply' | site_apply, referral, imported |
| status                 | varchar(30) default 'submitted'  | PHP string-backed enum: submitted, viewed, shortlisted, interviewing, offered, hired, rejected, withdrawn |
| employer_notes         | text nullable                    | internal use |
| applied_at             | timestamp                        |       |
| last_status_changed_at | timestamp nullable               |       |
| created_at             | timestamp                        |       |
| updated_at             | timestamp                        |       |

**Indexes**

- unique(job_listing_id, user_id)
- index(user_id, applied_at)
- index(job_listing_id, status)

## `application_status_histories`

| Field              | Type                           | Notes |
| ------------------ | ------------------------------ | ----- |
| id                 | bigint PK                      |       |
| application_id     | bigint FK -> applications.id   |       |
| old_status         | varchar(30) nullable           | PHP string-backed enum matching applications.status |
| new_status         | varchar(30)                    | PHP string-backed enum matching applications.status |
| changed_by_user_id | bigint FK nullable -> users.id |       |
| note               | text nullable                  |       |
| created_at         | timestamp                      |       |
| updated_at         | timestamp                      |       |

**Indexes**

- index(application_id, created_at)

## `saved_jobs`

| Field          | Type                         | Notes |
| -------------- | ---------------------------- | ----- |
| user_id        | bigint FK -> users.id        |       |
| job_listing_id | bigint FK -> job_listings.id |       |
| created_at     | timestamp                    |       |
| updated_at     | timestamp                    |       |

**Primary Key**

- composite PK(user_id, job_listing_id)

## `followed_companies`

| Field      | Type                      | Notes |
| ---------- | ------------------------- | ----- |
| user_id    | bigint FK -> users.id     |       |
| company_id | bigint FK -> companies.id |       |
| created_at | timestamp                 |       |
| updated_at | timestamp                 |       |

**Primary Key**

- composite PK(user_id, company_id)

## `notifications`

> **Use Laravel's built-in notification system.** Run `vendor/bin/sail artisan notifications:table --no-interaction` to generate the
> standard `notifications` migration. This provides the `id`, `type`, `notifiable_type`, `notifiable_id`,
> `data` (JSON), `read_at`, and `created_at` / `updated_at` columns out of the box.
>
> This table is a framework exception to the bigint PK convention above.
>
> Notification classes (e.g. `ApplicationSubmitted`, `ApplicationStatusChanged`, `SavedSearchAlert`)
> should extend `Illuminate\Notifications\Notification` and use the `database` + `mail` channels as needed.
> No custom table schema is required.

## `company_reviews`

| Field                | Type                           | Notes                                        |
| -------------------- | ------------------------------ | -------------------------------------------- |
| id                   | bigint PK                      |                                              |
| company_id           | bigint FK -> companies.id      |                                              |
| user_id              | bigint FK nullable -> users.id |                                              |
| rating               | decimal(2,1)                   |                                              |
| pros                 | text nullable                  |                                              |
| cons                 | text nullable                  |                                              |
| advice_to_management | text nullable                  |                                              |
| employment_status    | varchar(30) nullable           | current_employee, former_employee, candidate |
| review_status        | varchar(20) default 'pending'  | PHP string-backed enum: pending, published, rejected, hidden |
| published_at         | timestamp nullable             |                                              |
| created_at           | timestamp                      |                                              |
| updated_at           | timestamp                      |                                              |

**Indexes**

- index(company_id, review_status)
- index(company_id, rating)

# Phase 2 — Search Analytics & Retention

## `saved_searches`

| Field            | Type                        | Notes                              |
| ---------------- | --------------------------- | ---------------------------------- |
| id               | bigint PK                   |                                    |
| user_id          | bigint FK -> users.id       | created in Phase 1 app auth domain |
| name             | varchar(120) nullable       | optional label                     |
| keyword          | varchar(255) nullable       |                                    |
| filters_json     | json                        | normalized filters payload         |
| sort_by          | varchar(30) nullable        |                                    |
| frequency        | varchar(20) default 'daily' | daily, weekly                      |
| is_active        | boolean default true        |                                    |
| last_notified_at | timestamp nullable          |                                    |
| created_at       | timestamp                   |                                    |
| updated_at       | timestamp                   |                                    |

**Indexes**

- index(user_id, is_active)
- index(frequency)

## `search_queries`

| Field                  | Type                                  | Notes             |
| ---------------------- | ------------------------------------- | ----------------- |
| id                     | bigint PK                             |                   |
| user_id                | bigint FK nullable -> users.id        | anonymous allowed |
| session_id             | varchar(100) nullable                 |                   |
| keyword                | varchar(255) nullable                 |                   |
| filters_json           | json nullable                         |                   |
| result_count           | integer default 0                     |                   |
| latency_ms             | integer nullable                      |                   |
| clicked_job_listing_id | bigint FK nullable -> job_listings.id |                   |
| created_at             | timestamp                             | event time        |
| updated_at             | timestamp                             |                   |

**Indexes**

- index(user_id)
- index(created_at)
- index(clicked_job_listing_id)

## `job_impressions`

| Field           | Type                                    | Notes |
| --------------- | --------------------------------------- | ----- |
| id              | bigint PK                               |       |
| user_id         | bigint FK nullable -> users.id          |       |
| session_id      | varchar(100) nullable                   |       |
| search_query_id | bigint FK nullable -> search_queries.id |       |
| job_listing_id  | bigint FK -> job_listings.id            |       |
| rank_position   | integer nullable                        |       |
| created_at      | timestamp                               |       |
| updated_at      | timestamp                               |       |

**Indexes**

- index(job_listing_id, created_at)
- index(search_query_id)

## `job_clicks`

| Field           | Type                                    | Notes |
| --------------- | --------------------------------------- | ----- |
| id              | bigint PK                               |       |
| user_id         | bigint FK nullable -> users.id          |       |
| session_id      | varchar(100) nullable                   |       |
| search_query_id | bigint FK nullable -> search_queries.id |       |
| job_listing_id  | bigint FK -> job_listings.id            |       |
| rank_position   | integer nullable                        |       |
| created_at      | timestamp                               |       |
| updated_at      | timestamp                               |       |

**Indexes**

- index(job_listing_id, created_at)
- index(search_query_id)

# Phase 3 — Resume & Matching Readiness

## `resumes`

| Field           | Type                          | Notes                           |
| --------------- | ----------------------------- | ------------------------------- |
| id              | bigint PK                     |                                 |
| user_id         | bigint FK -> users.id         |                                 |
| file_name       | varchar(255)                  |                                 |
| file_path       | varchar(255)                  | private storage path            |
| mime_type       | varchar(100)                  |                                 |
| file_size_bytes | integer                       |                                 |
| is_default      | boolean default false         |                                 |
| visibility      | varchar(20) default 'private' | private, apply_only, searchable |
| parser_status   | varchar(20) default 'pending' | PHP string-backed enum: pending, success, failed |
| parsed_text     | longtext nullable             |                                 |
| parsed_json     | json nullable                 |                                 |
| uploaded_at     | timestamp                     |                                 |
| created_at      | timestamp                     |                                 |
| updated_at      | timestamp                     |                                 |

**Indexes**

- index(user_id, is_default)
- index(parser_status)
- index(visibility)

## `candidate_search_profiles`

| Field                    | Type                  | Notes                  |
| ------------------------ | --------------------- | ---------------------- |
| id                       | bigint PK             |                        |
| user_id                  | bigint FK -> users.id |                        |
| searchable               | boolean default false |                        |
| consented_at             | timestamp nullable    |                        |
| current_title            | varchar(180) nullable |                        |
| current_company          | varchar(180) nullable |                        |
| total_years_experience   | decimal(4,1) nullable |                        |
| preferred_locations_json | json nullable         |                        |
| preferred_roles_json     | json nullable         |                        |
| vector_status            | varchar(20) nullable  | PHP string-backed enum for future embedding state |
| created_at               | timestamp             |                        |
| updated_at               | timestamp             |                        |

**Indexes**

- unique(user_id)
- index(searchable)

## `candidate_contact_requests`

| Field                  | Type                                  | Notes                                |
| ---------------------- | ------------------------------------- | ------------------------------------ |
| id                     | bigint PK                             |                                      |
| candidate_user_id      | bigint FK -> users.id                 |                                      |
| company_id             | bigint FK -> companies.id             |                                      |
| requested_by_user_id   | bigint FK -> users.id                 | recruiter                            |
| related_job_listing_id | bigint FK nullable -> job_listings.id |                                      |
| status                 | varchar(20) default 'pending'         | PHP string-backed enum: pending, approved, declined, expired |
| message                | text nullable                         |                                      |
| responded_at           | timestamp nullable                    |                                      |
| created_at             | timestamp                             |                                      |
| updated_at             | timestamp                             |                                      |

**Indexes**

- index(candidate_user_id, status)
- index(company_id, created_at)

# Implementation Notes

1. **Phase order matters**  
   Phase 0 is enough to deliver the Elasticsearch MVP.  
   Phases 1, 2, and 3 prevent redesign when the product evolves.

2. **Denormalize for Elasticsearch, normalize for MySQL**  
   MySQL should remain relational and clean.  
   Elasticsearch documents should flatten company, skill, category, and location data for fast search.

3. **Enum storage convention**  
   This project defaults to PHP enums in code for bounded values.
   Use `varchar` plus PHP string-backed enums for every enum-like application-owned column, including statuses, roles, types, states, filters, labels, preferences, and source-like values.
   Do not use database enums for application-owned columns unless this schema doc is explicitly revised first.

4. **Multi-location jobs**  
   Start with `primary_location_id`.  
   If needed later, add `job_listing_locations`.

5. **Duplicate apply rule**  
   Keep `unique(job_listing_id, user_id)` at first.  
   Relax later only if multiple applications per job become a product requirement.

6. **Review and identity constraints**
   Decide whether company reviews should be one-per-user-per-company or allow multiple submissions over time, then encode that in a unique index or explicit policy.
   For analytics events, enforce the `user_id` or `session_id` requirement in application validation and, where practical, database constraints.
