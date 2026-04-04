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
2. each screen's design.md
3. each screen's code.html
4. screen.png only as visual reference

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

## Color Palette

- **Primary:** `#4338CA` - Indigo, used exclusively for active states, primary buttons, and matched text highlights
- **Background:** `#FAFAFA` - Stark off-white for main app background
- **Surface:** `#FFFFFF` - Cards, slide-overs, dropdowns
- **Text:** `#09090B` - Primary headings, active job titles
- **Muted:** `#71717A` - Secondary text, timestamps, placeholder copy
- **Borders:** `#E4E4E7` - Universal 1px solid structural lines
- **Accent:** `#EEF2FF` - Subtle indigo background for hover states and selected items

## Typography

Use **Geist** for structural readability and **Geist Mono** for precise data alignment. 

- **Headings:** Geist, 600, `18px`
- **Body:** Geist, 400, `14px`
- **Small text:** Geist, 400, `12px`
- **Metrics/Badges:** Geist Mono, 500, `11px`
- **Buttons:** Geist, 500, `13px`

**Style notes:** Crisp 1px solid borders and 0px border radius (`rounded-none`) for all structural layout elements to maintain a stark, crisp grid. Subtle softness is applied ONLY to dropdowns, overlays, and modals: these receive a slight border radius (`4px`) and soft shadows (`shadow-[0_4px_24px_rgba(0,0,0,0.08)]`) to clearly separate them from the underlying structured data.

## Design Tokens

```css
:root {
  --color-primary: #4338CA;
  --color-background: #FAFAFA;
  --color-text: #09090B;
  --color-muted: #71717A;
  --color-border: #E4E4E7;
  --color-accent: #EEF2FF;
  --font-primary: 'Geist', sans-serif;
  --font-mono: 'Geist Mono', monospace;
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
