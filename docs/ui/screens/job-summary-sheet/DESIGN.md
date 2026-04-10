# Detail Panel Current Spec

## Purpose

The detail panel is no longer a permanent split column. It is now a reusable summary sheet that can be opened from any results row across desktop, tablet, and mobile.

## Current Interaction Model

- Opens from row selection on the Search Results screen.
- Implemented with the existing shadcn `Sheet` component.
- Closing the sheet clears the selected row on the results page.
- Content swaps instantly when another job is selected.
- "View Full Details" routes to the dedicated job detail page instead of changing local page mode.

## Content Blocks

- Eyebrow label: `Job Overview`
- Job title and company block
- Two-column summary metrics grid
- Short role description
- Core proficiencies tag group
- Primary apply action
- Secondary full-details link action
- Bottom visual/location block

## Component Structure

- `resources/js/features/jobs/components/job-summary-sheet.tsx`
  Hosts both the `Sheet` wrapper and the summary content.
- `resources/js/features/jobs/components/highlighted-text.tsx`
  Shared text highlighting helper for list and detail surfaces.

## Visual Rules

- Use the same editorial, high-precision style as the results list.
- Keep the panel background lighter than the app shell but not shadow-heavy.
- Maintain sharp corners and subdued separators.
- Action styling should stay aligned with the indigo Larasearch language.

## Responsive Rules

- Desktop: right sheet overlay.
- Tablet: right sheet overlay.
- Mobile: right sheet overlay.

## Out Of Scope

- No apply flow persistence.
