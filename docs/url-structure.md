# URL Structure

## Canonical Public URLs

- `/` landing page
- `/jobs` jobs index and search results
- `/jobs/{job-slug}` job detail

## Code Naming

- Controller: `JobsController`
- Inertia page: `resources/js/pages/jobs/index.tsx`
- Inertia page: `resources/js/pages/jobs/show.tsx`
- Jobs feature entry: `JobsIndexContent`
- Jobs feature entry: `JobShowContent`

## Legacy Redirects

- `/search` redirects to `/jobs`
- `/search/jobs/{job}` redirects to `/jobs/{job}`

## Rules

- Use `/jobs` as the only canonical discovery path.
- Keep filter and sorting state in query parameters on `/jobs`.
- Do not nest job detail under `/search`.
- Keep Laravel starter-kit auth and settings URLs unchanged.

## Examples

- `/jobs?q=laravel&location=remote&sort=newest`
- `/jobs/senior-laravel-engineer`
