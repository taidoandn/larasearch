# Job Detail Current Spec

## Purpose

The Job Detail screen is the dedicated routed page for full reading mode. It is reached from the results summary sheet through "View Full Details".

## Route

- URL pattern: `/jobs/{job}`
- Laravel route name: `jobs.show`
- Inertia page component: `resources/js/pages/jobs/show.tsx`

## Current Layout

- Main reading column for overview, benchmark, responsibilities, requirements, and related jobs.
- Secondary sidebar column for company context, metadata, and the apply action.
- Reuses the default `AppLayout` and existing app shell.

## Component Structure

- `resources/js/pages/jobs/show.tsx`
  Route entry, breadcrumbs, and job lookup from mock data.
- `resources/js/features/jobs/components/show-content.tsx`
  Full detail page content.
- `resources/js/features/jobs/data/mock-search-data.ts`
  Shared mock data.
- `resources/js/features/jobs/components/shared.tsx`
  Shared detail sections, metadata rows, and label helpers.

## Navigation Behavior

- Back action returns to `/jobs`.
- Related opportunities link directly to other `/jobs/{job}` pages.
- The full detail page does not depend on a local "view mode" from the results page anymore.

## Visual Rules

- Typography-first composition.
- Strong headline hierarchy.
- Dense but restrained metadata sidebar.
- Indigo benchmark/action accents.
- Same light/dark visual language as the results page.

## Responsive Rules

- Desktop: two-column content plus sidebar.
- Tablet: same structure with natural vertical compression.
- Mobile: stacked reading layout with sidebar sections flowing below the main article.

## Out Of Scope

- No backend-driven detail fetching yet.
- No application submission workflow or save-state persistence.
