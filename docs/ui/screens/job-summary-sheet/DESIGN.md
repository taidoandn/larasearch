# Job Summary Sheet Specification

This screen must follow the canonical design system in [../DESIGN.md](../DESIGN.md). If any guidance here conflicts with the parent document, the parent document wins.

## Purpose

The summary sheet is a high-speed comparison panel opened from the jobs index. It should help the user assess fit without leaving the search flow.

## Structure

- Use a right-side panel with generous padding and clear internal grouping.
- Start with company identity and primary actions at the top.
- Follow with title, key role chips, match score block, technical requirements, location/context, and the deep-link CTA.

## Visual Rules

- The panel should feel connected to the jobs index, not like a separate modal universe.
- Keep surfaces soft and bright, with the same primary blue accent system as the index.
- Avoid dense walls of text; favor short blocks and grouped callouts.
- The match score should be a featured visual component.
- Technical requirements should render as pills or micro-cards.
- Location/map treatment should feel atmospheric and supportive, not overly literal.

## Actions

- `Apply` is the primary action.
- `Save` is secondary.
- `View Full Position Details` is the clear escape hatch into the full detail page.

## Constraints

- Keep the panel narrow enough to preserve the context of the underlying list on desktop.
- Do not rely on deep nested sections or divider-heavy composition.
