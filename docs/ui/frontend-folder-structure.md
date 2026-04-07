# Frontend Folder Structure

## Goal

Keep Laravel starter-kit code in its original structure, and give custom product domains a real home without rewriting the starter kit.

## Structure

```text
resources/js
├── pages/                  # Inertia route entrypoints
├── layouts/                # Existing layout shells
├── features/               # Custom app domains only
│   ├── jobs/
│   └── landing/
├── components/
│   ├── ui/                 # shadcn primitives and thin wrappers
│   └── ...                 # Laravel starter-kit and shared app components
├── hooks/                  # Existing shared hooks
├── lib/                    # Pure helpers
└── types/                  # App-wide contracts
```

## Rules

- Leave Laravel starter-kit auth, settings, app-shell, and shared utility components where the starter kit put them unless there is a strong reason to change them.
- `features/<name>/` is for custom product domains you added to the app.
- `pages/` should stay thin and compose layouts plus feature entrypoints.
- `components/ui/` stays app-agnostic.
- If code belongs only to a custom domain like jobs or landing, keep it in that feature.
- If code is part of starter-kit flows like auth, settings, profile, or two-factor, keep it in the starter-kit structure.

## Import Rules

- `pages/*` may import from `layouts`, `features`, `components`, `hooks`, `lib`, and `types`.
- `features/*` may import from `components/ui`, `lib`, and their own local files.
- Do not move starter-kit internals into `features/`.
- Do not import private files across features.
- Avoid large global barrel files. Feature `index.ts` files are acceptable as a small public API.

## Current Domain Mapping

- `features/jobs/` owns search results, job detail, and mock job data used by those views.
- `features/landing/` owns the landing page sections.
- Laravel starter-kit auth/settings/app-shell code remains in `components/`, `hooks/`, and `layouts/`.

## Anti-Patterns

- Adding business logic directly to `pages/`.
- Moving starter-kit files into `features/` just for consistency.
- Importing feature internals from another feature.
- Putting app-specific widgets in `components/ui/`.
- Mixing custom domain code back into a flat `components/` bucket.

## Refactor Notes

- This structure preserves Inertia's `pages/**/*.tsx` route resolution.
- Existing route files were kept in place.
- Only custom app domains were moved into `features/`.
- Starter-kit files were restored to their original structure.
