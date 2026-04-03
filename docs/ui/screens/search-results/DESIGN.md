# Search Results Current Spec

## Purpose

The Search Results screen is the primary browsing surface inside the existing Laravel app shell. It only owns the page content area and must not recreate layout, sidebar, or global navigation concerns.

## Current Interaction Model

- Desktop, tablet, and mobile all keep the results list full width by default.
- Selecting a job opens the summary view in a right-side sheet.
- Closing the sheet clears the selection.
- Selecting another row swaps the sheet content to the newly selected job.
- The results page never morphs into a split desktop layout anymore.
- "View Full Details" navigates to the dedicated job detail page at `/search/jobs/{job}`.

## Layout

- Top section: dense filter/search bar with five controls and a primary search action.
- Secondary row: active filter chips and reset action.
- Toolbar row: result count and sort control.
- Main body: mapped results rows and pagination footer.
- Overlay layer: sheet-based summary panel for the selected job.

## Component Structure

- `resources/js/pages/search-results.tsx`
  Page entry using the default `AppLayout`.
- `resources/js/components/search-results/index.tsx`
  Screen orchestrator for selection state and sheet open/close behavior.
- `resources/js/components/search-results/search-filters.tsx`
  Controlled filter inputs/selects and dynamic chip rendering.
- `resources/js/components/search-results/results-toolbar.tsx`
  Controlled sort select.
- `resources/js/components/search-results/results-list.tsx`
  Reusable mapped row rendering and pagination footer.
- `resources/js/components/search-results/summary-panel.tsx`
  Sheet-hosted summary panel shown for any selected job.
- `resources/js/components/search/mock-search-data.ts`
  Mock data source shared by results and detail screens.

## Input And Select Behavior

- Keyword and location are controlled `Input` components using the same visual height and typography baseline as the `Select` triggers.
- Work model, experience, salary, and sort use shadcn `Select`.
- Filter chips are derived from current control values instead of being static markup.
- Reset restores the initial filter values and chip set.

## Visual Rules

- Preserve the existing Larasearch architectural-ledger feel: sharp edges, dense metrics, low-shadow surfaces, indigo action accents.
- Use shadcn UI primitives for interaction controls instead of raw form buttons and selects.
- Keep light and dark mode support with the same hierarchy and contrast expectations.
- Selected rows use an owned left border and tonal background, not floating absolute accents.

## Responsive Rules

- Desktop: full-width list plus right sheet when selected.
- Tablet: same behavior as desktop.
- Mobile: same selection sheet behavior for consistency with the current implementation.

## Out Of Scope

- No backend search, filter, save, apply, or pagination logic yet.
- No layout-level changes to the Laravel starter shell.
