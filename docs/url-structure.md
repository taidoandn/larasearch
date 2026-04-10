# URL Structure

## Canonical Public URLs

- `/` landing page
- `/jobs` jobs index and search results
- `/jobs/{job-slug}` job detail

## Code Naming

- Controller: `JobsController`
- Inertia page: `resources/js/pages/jobs/index.tsx`
- Inertia page: `resources/js/pages/jobs/show.tsx`
- Jobs list components live under `resources/js/features/jobs/components/`
- Job detail components live under `resources/js/features/jobs/components/`

## Rules

- Use `/jobs` as the only canonical discovery path.
- Keep filter and sorting state in query parameters on `/jobs`.
- Keep Laravel starter-kit auth and settings URLs unchanged.

## Examples

- `/jobs?q=laravel&location=remote&sort=newest`
- `/jobs/senior-laravel-engineer`
