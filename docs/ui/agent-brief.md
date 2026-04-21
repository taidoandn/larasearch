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
2. docs/ui/screens/DESIGN.md
3. each screen's DESIGN.md
4. each screen's code.html
5. screen.png only as visual reference

Do not treat screenshots as the primary source of truth if they conflict with markdown specs.
If a screen-level DESIGN.md conflicts with docs/ui/screens/DESIGN.md, the top-level screens design system wins.

## Branding
- Product name: Larasearch

## Design direction
- The Digital Curator
- editorial, high-trust product experience
- soft layered surfaces instead of hard structural borders
- authoritative electric blue accent
- high scanability with calm information density
- production-ready, reusable UI

## Color Palette

- **Primary:** `#004ac6` - Authoritative electric blue for active states, CTAs, and fit cues
- **Primary Container:** `#2563eb` - brighter interaction blue for gradients and emphasis
- **Background:** `#f7f9fb` - soft cool canvas for the overall product
- **Surface Low:** `#f2f4f6` - section panels, rails, and grouped surfaces
- **Surface:** `#ffffff` - cards, overlays, and focused content
- **Text:** `#191c1e` - primary headings and high-priority content
- **Muted:** `#434655` - secondary text and metadata
- **Accent:** `rgba(255,255,255,0.7)` - glassy floating surfaces when needed

## Typography

Use **Manrope** for high-impact display and **Inter** for functional reading.

- **Display:** Manrope, 700
- **Headings:** Manrope, 600
- **Body:** Inter, 400, `14px`
- **Small text:** Inter, 400, `12px`
- **Metrics/Badges:** Inter, 500, `11px`
- **Buttons:** Inter, 500, `13px`

**Style notes:** Prefer tonal layering over visible structural borders. Cards and interactive surfaces should feel soft and elevated, usually with generous radius. Avoid hard-edged grid styling unless a screen-specific spec explicitly calls for it.

## Design Tokens

```css
:root {
  --color-primary: #004ac6;
  --color-primary-container: #2563eb;
  --color-background: #f7f9fb;
  --color-surface-low: #f2f4f6;
  --color-surface: #ffffff;
  --color-text: #191c1e;
  --color-muted: #434655;
  --font-display: 'Manrope', sans-serif;
  --font-body: 'Inter', sans-serif;
}
```

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
- Search/filter experience should feel like a premium discovery workspace
- Results and filters should read as layered editorial panels, not raw app scaffolding
- Selecting a job opens a summary detail panel
- Full job detail is a dedicated page
- Mobile uses full-screen detail instead of split view

## Expected output from agent
- clean component structure
- minimal but clear comments
- typed props where appropriate
- no unnecessary dependencies
- summary of created files after each task
