# URL Structure

## Canonical Public URLs

- `/` landing page
- `/jobs` jobs index and search results
- `/jobs/{job-slug}` job detail

## Code Naming

- Controller: `JobsController`
- Inertia page wrapper: `resources/js/pages/jobs/index.tsx`
- Inertia page wrapper: `resources/js/pages/jobs/show.tsx`
- Jobs search screen: `resources/js/features/jobs/screens/search-screen.tsx`
- Job detail screen: `resources/js/features/jobs/screens/detail-screen.tsx`
- Jobs search components live under `resources/js/features/jobs/components/search/`
- Job detail components live under `resources/js/features/jobs/components/detail/`
- Shared jobs-only components live under `resources/js/features/jobs/components/shared/`

## Rules

- Use `/jobs` as the only canonical discovery path.
- Keep filter and sorting state in query parameters on `/jobs`.
- Keep Laravel starter-kit auth and settings URLs unchanged.

## Examples

- `/jobs?q=laravel&location=remote&sort=newest`
- `/jobs/senior-laravel-engineer`
