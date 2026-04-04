# Design System Specification: High-End Enterprise Minimal

## 1. Overview & Creative North Star
### The Creative North Star: "The Architectural Ledger"
This design system rejects the "bubbly" consumer web in favor of a high-precision, editorial aesthetic inspired by Swiss typography and modern architectural drafting. It is designed for high-density information environments where clarity is synonymous with luxury. 

We move beyond the "template" look by utilizing **Extreme Structural Sharpness** (0px radii) contrasted against **Floating Softness** (4px radius modals). The interface should feel like a physical stack of high-quality vellum paper—precise, intentional, and authoritative. By eliminating traditional borders in favor of tonal shifts, we create a "borderless" flow that guides the eye through hierarchy rather than boxes.

---

## 2. Colors & Surface Logic
The palette is a disciplined monochromatic study punctuated by a singular, high-intellect Indigo.

### The Color Tokens
- **Base:** `background: #f9f9f9` | `surface: #ffffff`
- **Accent:** `primary: #5148d7` (Indigo) | `on-primary: #faf6ff`
- **Typography:** `on-surface: #2d3435` (Deep Charcoal) | `on-surface-variant: #5a6061` (Subtle Slate)
- **Status:** `error: #9e3f4e` | `success: #4338ca` (Utilizing Indigo for positive actions to maintain brand cohesion).

### The "No-Line" Rule
Explicitly prohibit 1px solid borders for sectioning layout elements. Boundaries are defined solely through background shifts.
- **Example:** A `surface-container-low` (#f2f4f4) sidebar sitting directly against a `surface` (#f9f9f9) main content area.
- **The Signature Texture:** Use a 1% noise texture overlay or a subtle linear gradient (`primary` to `primary-dim`) on primary CTAs to avoid the "flat-UI" trap and add professional "soul."

### Surface Hierarchy (Nesting)
Treat the UI as physical layers. Use the following stack to create depth without shadows:
1. **Level 0 (Base):** `surface` (#f9f9f9) - The desk.
2. **Level 1 (Sections):** `surface-container-low` (#f2f4f4) - The paper.
3. **Level 2 (Cards):** `surface-container-lowest` (#ffffff) - The focus.

---

## 3. Typography
We use **Geist** for its mathematical purity and **Geist Mono** for data-heavy metrics to evoke a sense of "Engineered Intelligence."

| Role | Token | Font | Size | Case/Tracking |
| :--- | :--- | :--- | :--- | :--- |
| **Display** | `display-md` | Geist Sans | 2.75rem | -0.02em tracking |
| **Headline** | `headline-sm` | Geist Sans | 1.5rem | Semi-bold, tight |
| **Data Metric**| `label-md` | Geist Mono | 0.75rem | Monospace, tabular |
| **Body Content**| `body-md` | Geist Sans | 0.875rem | Regular, 1.5 line-height |
| **Small Label** | `label-sm` | Geist Sans | 0.6875rem | All Caps, +0.05em spacing |

---

## 4. Elevation & Depth
### The Layering Principle
Depth is achieved through **Tonal Layering**. In this system, shadows are rare and intentional.
- **Ambient Shadows:** Only for floating elements (Modals/Popovers). Use `0 20px 40px -12px rgba(9, 9, 11, 0.06)`. This mimics soft, natural light rather than a digital drop shadow.
- **The "Ghost Border" Fallback:** If high-contrast accessibility is required, use `outline-variant` (#adb3b4) at **10% opacity**. It should be felt, not seen.
- **Glassmorphism:** Use `backdrop-blur-md` with `bg-white/70` for the TopBar to allow content to bleed through, maintaining a sense of place as the user scrolls.

---

## 5. Components & Implementation (Tailwind CSS v4 + React 19)

### TopBar (The Digital Anchor)
The TopBar follows an Airbnb-style "Center-Search" layout but with enterprise rigidity. 
- **Style:** `0px` border-bottom, `bg-white/80`, `backdrop-blur-xl`.
- **Layout:** [Brand block with stronger product mark] — [Center utility strip showing search mode plus core nav] — [Global Actions/Profile].
- **Implementation:** Sticky position with a `z-index` of 50. Use `h-16` for a slim, editorial profile.

### Hero Search Entry
- **Purpose:** The hero must read like a real product entry point, not a decorative marketing input.
- **Structure:** Eyebrow label, dense headline/subheadline pair, then a search shell with `Primary Query`, a `Precision Match` status, underlined query field, compact signal chips, and a product CTA.
- **Support Metrics:** Add a compact row of 2-3 mono metric tiles directly under the search shell to reduce empty space and reinforce search density.

### Search Preview
- **Sidebar:** Treat the left column as a compact filter ledger with labeled values and signal chips, using tonal separation rather than visible dividers.
- **Toolbar:** Include indexed-results metadata and a visible sort state (`Precision Match`) to mirror the real product’s search toolbar.
- **Rows:** Increase density so each result includes title/company, compact metadata, summary text, mono metrics, and right-aligned added/save/chevron affordances.

### Query Examples
- **Tone:** These cards should read like realistic search prompts users would actually submit.
- **Presentation:** Add small mono query labels and keep the cards tight, readable, and operational rather than inspirational.

### Technical Foundation
- **Relationship:** The dark section should feel like the same product shell in night mode, not a separate campaign.
- **Insight Panel:** The right column should resemble a system-insight panel with a labeled execution trace, code block, and a few operational metrics.

### JobCard (The Dense Ledger)
- **Style:** `bg-white`, `p-4`, `0px` radius. 
- **Structure:** Use a three-column grid. Left: Job Title/Company (`title-md`). Center: Key Metrics in `Geist Mono` (`label-md`). Right: Status Chip and Time.
- **Interaction:** On hover, shift background to `surface-container-low` (#f2f4f4). Do not use shadows.

### SummaryPanel & FullDetailView
- **SummaryPanel (Right-side split):** This is a fixed-width (`w-96`) vertical column. Use `bg-f9f9f9` to separate it from the main feed. Use `pt-10` spacing to create an "asymmetric breath" at the top of the panel.
- **FullDetailView:** Typography-first. Use `headline-lg` for the job title. Ensure `max-w-3xl` for optimal reading length. Eliminate all dividers; use `spacing-8` to separate sections.

### Buttons & Inputs
- **Primary Button:** `bg-indigo-700`, `text-white`, `rounded-none`, `px-6`, `py-3`. State: `hover:bg-indigo-800`.
- **Input Fields:** `bg-transparent`, `border-b border-E4E4E7`, `rounded-none`, `focus:border-indigo-700`. No box borders—only the bottom hairline for an "underlined ledger" look.

---

## 6. Do's and Don'ts

### Do
- **Use White Space as a Separator:** Increase vertical padding (`py-10`) between unrelated content blocks rather than inserting lines.
- **Leverage Asymmetry:** Place metrics in the SummaryPanel with intentional alignment—perhaps all right-aligned—to create a signature professional look.
- **Maintain 0px Radius:** Every layout container must be sharp. Only use 4px for elements that literally "sit on top" of the UI (Modals/Tooltips).

### Don't
- **Don't use Grey Text on Grey Backgrounds:** Maintain high contrast. Use `09090B` for all primary text to ensure authority.
- **Don't use Standard Shadows:** Avoid the Tailwind default `shadow-md`. Use the custom ambient shadow defined in Section 4.
- **Don't use 1px Dividers:** If you feel a divider is needed, try using a 20px gap (`gap-5`) or a 2px background color block instead.

---

## 7. Technical Configuration (Tailwind CSS v4)
