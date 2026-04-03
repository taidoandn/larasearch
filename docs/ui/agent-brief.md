# Larasearch UI Agent Brief

## Product
Larasearch is a smart job search platform focused on fast, high-density search UX for recruiters and analysts.

## Stack
- Laravel 12
- React 19
- Inertia.js v2
- Tailwind CSS v4
- TypeScript preferred

## Source of truth priority
When implementing UI, follow this priority order:
1. docs/ui/foundations/implementation-rules.md
2. docs/ui/foundations/responsive-rules.md
3. each screen's design.md
4. each screen's code.html
5. screen.png only as visual reference

Do not treat screenshots as the primary source of truth if they conflict with markdown specs.

## Branding
- Product name: Larasearch

## Design direction
- Enterprise Minimal
- high scanability
- crisp borders
- restrained indigo accent
- dense but readable layout
- production-ready, reusable UI

## Engineering goals
- Build reusable components
- Keep code implementation-friendly
- Avoid one-off page-specific hacks
- Prefer shared layouts and props-driven components
- Keep responsive behavior predictable

## Page architecture
Main screens:
- Search Results page
- Job Detail Summary Panel
- Full Job Detail page
<!-- - Benchmark / Insights panel -->

## Important UX rules
- Search/filter bar stays at the top
- Results list is full-width by default
- Selecting a job opens a summary detail panel
- Full job detail is a dedicated page
- Mobile uses full-screen detail instead of split view

## Expected output from agent
- clean component structure
- minimal but clear comments
- typed props where appropriate
- no unnecessary dependencies
- summary of created files after each task
