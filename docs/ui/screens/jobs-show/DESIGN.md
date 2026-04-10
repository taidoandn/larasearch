# Job Detail Current Spec

## Purpose

The Job Detail screen is the dedicated routed page for full reading mode. It is reached from the results summary sheet through "View Full Details".

## Route

- URL pattern: `/jobs/{job}`
- Laravel route name: `jobs.show`
- Inertia page component: `resources/js/pages/jobs/show.tsx`

## Current Layout

- Main reading column for overview, responsibilities, requirements, and related jobs.
- Secondary sidebar column for company context, metadata, and the apply action.
- Reuses the default `AppLayout` and existing app shell.

## Component Structure

- `resources/js/pages/jobs/show.tsx`
  Route entry, breadcrumbs, and backend-provided job props.
- `resources/js/features/jobs/components/job-header.tsx`
  Headline, salary, and primary actions.
- `resources/js/features/jobs/components/job-detail-info.tsx`
  Main reading column, metadata sidebar, and related opportunities.
- `resources/js/features/jobs/components/job-detail-section.tsx`
  Reusable content section wrapper.

## Navigation Behavior

- Back action returns to `/jobs`.
- Related opportunities link directly to other `/jobs/{job}` pages.
- The full detail page does not depend on a local "view mode" from the results page anymore.

## Visual Rules

- Typography-first composition.
- Strong headline hierarchy.
- Dense but restrained metadata sidebar.
- Indigo action accents.
- Same light/dark visual language as the results page.

## Responsive Rules

- Desktop: two-column content plus sidebar.
- Tablet: same structure with natural vertical compression.
- Mobile: stacked reading layout with sidebar sections flowing below the main article.

## Out Of Scope

- No application submission workflow or save-state persistence.
