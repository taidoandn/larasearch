# Jobs Show Screen Specification

This screen must follow the canonical design system in [../DESIGN.md](../DESIGN.md). If any guidance here conflicts with the parent document, the parent document wins.

## Purpose

The job detail screen is the editorial reading surface for a single role. It should feel more premium and narrative than the index: less like filtering, more like evaluating fit.

## Layout

- Use an asymmetrical content split.
- Main column carries title, overview, responsibilities, requirements, skills, and related roles.
- Right column is a sticky contextual rail for company information, summary metrics, map/context, and apply action.
- Preserve strong breathing room between major content sections.

## Header

- Begin with a prominent title block using the primary headline scale.
- Show company, location, and role metadata directly beneath the title.
- Keep primary and secondary actions visible at the header level.
- The header should feel like a hero panel rather than a thin page heading.

## Main Content

- Present overview first in readable body text width.
- Responsibilities should render as separated editorial bullets or soft list blocks.
- Requirements should use card or tile treatment, not table-like dividers.
- Skills should render as compact monochrome or lightly tinted pills.
- Related roles should appear as a compact secondary card grid at the bottom.

## Right Rail

- The company block should sit inside its own card.
- Summary metrics should be grouped in a second card.
- Map/location context and apply button can share a third card if composition stays clean.
- Keep the apply CTA high-contrast and always visually dominant inside the rail.

## Tone

- This page should feel calmer and more spacious than the jobs index.
- Avoid overloading the rail with too many visual treatments.
- The hierarchy must clearly prioritize:
  role title, compensation/fit summary, overview, then supporting company context.
