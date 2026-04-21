# Jobs Index Screen Specification

This screen must follow the canonical design system in [../DESIGN.md](../DESIGN.md). If any guidance here conflicts with the parent document, the parent document wins.

## Purpose

The jobs index is the primary discovery workspace. It should feel like a refined search console rather than a generic list page: fast to scan, calm under dense information, and clearly optimized for narrowing and comparison.

## Layout

- Use a wide two-column layout.
- Left column is a persistent filter rail with a compact, fixed width.
- Right column is the search-results workspace with toolbar, cards, and pagination.
- Keep the page centered in a broad desktop container; avoid edge-to-edge stretching.

## Filter Rail

- The sidebar should read as a soft, integrated panel sitting on the canvas, not a separate app shell.
- Start with `Filters` and a short helper line like `Refine your match`.
- Order controls as:
  `Keywords`, `Location`, `Category`, `Salary Range`, `Work Model`, `Experience`, `Skills`.
- Text inputs should be rounded, borderless, and sit on `surface-container-lowest`.
- Salary range should present as a visual slider treatment first, with numeric anchors secondary.
- Work model and experience should prefer checklist rows with counts when facet counts exist.
- Skills should appear as selected pills plus a lightweight entry affordance.

## Results Toolbar

- The top toolbar should summarize result count and active filters before the sort control.
- Present active filters as removable pills in a single horizontal row when possible.
- Include a visible `Clear all filters` action below or beside the active pill row.
- Sort should live in a compact floating control aligned right.
- Keep supporting metadata in mono label styling.

## Result Cards

- Cards must use the soft editorial card treatment from the design system: large radius, pale surfaces, minimal visible lines.
- The first row should be visually featured by default.
- Card anatomy should be:
  logo/avatar, title, company/location line, compact metadata row, skills row, right-edge match/bookmark area.
- Title should dominate the card visually.
- Company/location metadata should remain lighter and secondary.
- The compensation item should use primary brand emphasis.
- Skills should render as small monochrome pills.
- The match score should appear as a circular score treatment at the far right.
- Bookmark affordance should sit near the match score, not inside the metadata cluster.

## Interaction

- Hover should slightly lift or brighten cards without introducing harsh shadows.
- Selected cards should use a stronger primary outline and slightly tinted background.
- Pagination should sit in its own soft container below the results list.

## Mobile Behavior

- Collapse the two-column layout into a single column.
- Move filters above results in mobile.
- Keep the toolbar readable with stacked rows rather than squeezing chips into a single cramped line.
