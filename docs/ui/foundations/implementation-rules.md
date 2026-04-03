# Implementation Rules

## General

- Preserve the Larasearch design language.
- Do not redesign unless explicitly asked.
- Reuse styles and components whenever possible.
- Keep the codebase easy to scale.

## UI translation rules

- Convert each screen into reusable React + Inertia components.
- Use code.html as a structural Tailwind reference, not as final copy-paste output.
- Refactor repeated patterns into shared components.
- Do not keep raw static HTML blobs inside page files.

## Component rules

- Prefer small reusable components over large monolith files.
- Keep components colocated by domain when useful.
- Use shared primitive UI components when patterns repeat.

## Styling rules

- Use Tailwind utilities only unless absolutely necessary.
- Avoid custom CSS unless a pattern cannot be expressed cleanly in Tailwind.
- Keep spacing on a 4px scale.
- Keep borders crisp and visual hierarchy subtle.

## State rules

- Keep UI state predictable and explicit.
- Prefer props-driven rendering.
- Avoid hidden magic logic.
- For page mode changes, use explicit state such as selectedJobId, isBenchmarkOpen, filters, query.

## Data rules

- Use mock/sample data only when backend data is not ready.
- Keep sample data structured and realistic.
- Do not hardcode display logic too tightly to mock values.

## Laravel / Inertia rules

- Follow Laravel + Inertia conventions.
- Do not introduce Next.js or SPA-only assumptions.
- Keep page components under resources/js/pages.
- Keep layouts under resources/js/layouts.
- Keep reusable components under resources/js/components.

## Accessibility rules

- Use semantic HTML.
- Inputs must have accessible labels.
- Buttons and interactive controls must have clear states.
- Keyboard accessibility should be preserved.

## Output rules

After each task, provide:

1. files created/updated
2. what was implemented
3. what remains
4. any assumptions made
