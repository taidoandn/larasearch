# Design System Strategy: Larasearch

This document is the canonical design-system source of truth for all files under `docs/ui/screens/`. Screen-specific `DESIGN.md` files must extend this strategy, not replace it. They should only describe screen-level application details, layout priorities, and content hierarchy.

## 0. Documentation Rule

- `docs/ui/screens/DESIGN.md` defines the shared visual system.
- `docs/ui/screens/<screen>/DESIGN.md` defines how that system is applied to a specific screen.
- Screen-specific docs must not introduce a competing design language unless they explicitly call out a constrained variation inside the same family.
- If a screen doc conflicts with this document, this document wins.

## 1. Overview & Creative North Star

### The Creative North Star: "The Digital Curator"

This design system rejects the cluttered, high-density patterns of traditional job boards in favor of an editorial, high-trust experience. We are not building a database; we are building a premium match-making environment. The system draws inspiration from the precision of **Stripe** and the spatial intelligence of **Linear**.

**The Editorial Shift:** To move beyond the "SaaS template" look, we utilize intentional asymmetry and extreme white space. Content is grouped into logical "islands" rather than rigid, edge-to-edge grids. By using a high-contrast typography scale and "layered" surfaces, we guide the user’s eye toward what matters most: the opportunity, the salary, and the fit.

---

## 2. Colors & Tonal Depth

Our palette is anchored in an "Electric Blue" that conveys modern energy, supported by a sophisticated range of neutral surfaces that provide architectural structure without the need for heavy lines.

### Key Tokens

- **Primary:** `#004ac6` (The Authoritative Accent)
- **Primary Container:** `#2563eb` (The Interaction Core)
- **Background:** `#f7f9fb` (The Canvas)
- **Surface Lowest:** `#ffffff` (The Floating Layer)

### The "No-Line" Rule

**Prohibit 1px solid borders for sectioning.** To achieve a high-end feel, boundaries must be defined solely through background color shifts. Use `surface-container-low` sections sitting on a `surface` background to create separation. This creates a "soft UI" that feels integrated and organic rather than boxed-in.

### Surface Hierarchy & Nesting

Treat the UI as physical layers.

- **Level 0 (Base):** `surface` (`#f7f9fb`)
- **Level 1 (Sections):** `surface-container-low` (`#f2f4f6`)
- **Level 2 (Interactive Cards):** `surface-container-lowest` (`#ffffff`)

### The Glass & Gradient Rule

For floating elements, such as navigation bars or "Quick Apply" modals, use **Glassmorphism**:

- `background: rgba(255, 255, 255, 0.7)`
- `backdrop-blur: 12px`
  Main Action CTAs should utilize a subtle linear gradient from `primary` to `primary_container` (150° angle) to provide a "tactile" soul that flat colors cannot achieve.

---

## 3. Typography: The Editorial Voice

We pair **Manrope** for high-impact display moments with **Inter** for functional reading. This combination signals both "Technology" and "Humanity."

| Level        | Token         | Font    | Weight | Character                             |
| :----------- | :------------ | :------ | :----- | :------------------------------------ |
| **Display**  | `display-lg`  | Manrope | 700    | Dramatic, used for Hero stats.        |
| **Headline** | `headline-md` | Manrope | 600    | Authoritative job titles.             |
| **Title**    | `title-md`    | Inter   | 600    | High-trust functional headers.        |
| **Body**     | `body-md`     | Inter   | 400    | Optimized for long-form descriptions. |
| **Label**    | `label-sm`    | Inter   | 500    | Metadata & Chips (all-caps allowed).  |

---

## 4. Elevation & Depth: Tonal Layering

Traditional drop shadows are often "muddy." This system uses light and color to define depth.

- **The Layering Principle:** Instead of a shadow, place a `surface-container-lowest` card on a `surface-container-low` section. This provides a natural, "paper-on-desk" lift.
- **Ambient Shadows:** For floating modals or "active" cards, use extra-diffused shadows: `0px 20px 40px rgba(0, 74, 198, 0.06)`. Note the 6% opacity and the blue tint—this mimics natural light refraction through the primary brand color.
- **The Ghost Border:** If a border is required for accessibility in input fields, use `outline-variant` at **15% opacity**. Never use a 100% opaque border.

---

## 5. Components

### Cards & Lists

- **Forbid Divider Lines:** Separate job listings with vertical white space (`spacing-32`) or subtle background shifts.
- **Corner Radius:** Cards must use `xl` (1.5rem / 24px) to feel soft and approachable.
- **Nesting:** Nested data points (like "Skills") should sit on a `surface-variant` background with a `sm` radius.

### Buttons

- **Primary:** High-gloss gradient (`primary` to `primary_container`). Large padding (16px 32px).
- **Secondary:** No background. `primary` text with a "Ghost Border" that appears only on hover.
- **Tertiary:** `surface-container-high` background with `on-surface` text.

### Elegant Input Fields

- **Styling:** `surface-container-lowest` background. No border.
- **State:** On focus, use a 2px "Electric Blue" glow with a 4px blur, rather than a hard stroke.
- **Metadata Chips:** Use `label-sm` text. Chips should be monochromatic (soft grays) to keep the focus on the primary CTA.

### The "Match Score" Component (Contextual Addition)

For a job platform, trust is key. Create a custom "Match Score" ring using a conic gradient of the `primary` color, nested within a glassmorphic container to show user compatibility.

---

## 6. Do's and Don'ts

### Do

- **Do** use asymmetrical layouts (e.g., a wide 2/3 column for Job Description and a narrow 1/3 for Company Info) to create an editorial feel.
- **Do** use `on-surface-variant` (soft gray) for secondary labels to create clear information hierarchy.
- **Do** allow elements to overlap slightly to create a sense of depth and "physicality."

### Don't

- **Don't** use 1px black or dark gray dividers. It breaks the "premium" illusion.
- **Don't** use standard "drop shadows" (0,0,0,0.2). They look dated and dirty.
- **Don't** cram information. If a card feels full, increase the padding (`xl` scale) rather than shrinking the text.
- **Don't** use sharp corners. Everything in this system is designed to feel "Soft-Modern," requiring a minimum of `md` (0.75rem) radius for even the smallest elements.
