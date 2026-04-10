<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Services\JobSearchFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class JobShowController extends Controller
{
    public function __invoke(Request $request, JobListing $job): Response
    {
        abort_unless($job->isVisibleInSearch(), 404);

        $job->loadMissing(['company', 'primaryLocation', 'categories', 'skills']);

        return Inertia::render('jobs/show', [
            'job' => $this->jobPayload($job),
            'relatedJobs' => $this->relatedJobsPayload($job),
            'searchContext' => [
                'index_query' => JobSearchFilters::compact($request->query()),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function jobPayload(JobListing $job): array
    {
        $primaryLocation = $job->primaryLocation?->display_name;
        $companySummary = (string) ($job->company?->description ?? '');
        $companyMeta = collect([
            $job->company?->industry,
            $job->company?->company_size,
            $job->company?->country_code,
        ])->filter()->implode(' • ');

        return [
            'id' => $job->getKey(),
            'slug' => $job->slug,
            'title' => $job->title,
            'application_url' => $job->application_url,
            'company' => [
                'name' => $job->company?->name,
                'slug' => $job->company?->slug,
                'summary' => $companySummary,
                'meta' => $companyMeta,
                'website' => $job->company?->website_url,
            ],
            'primary_location' => $primaryLocation,
            'locations' => $primaryLocation === null ? [] : [$primaryLocation],
            'job_type' => $job->job_type?->value,
            'skills' => $job->skills->pluck('name')->values()->all(),
            'salary' => [
                'min' => $job->salary_min,
                'max' => $job->salary_max,
                'currency' => $job->salary_currency,
                'is_visible' => $job->salary_is_visible,
            ],
            'work_model' => $job->work_model?->value,
            'overview' => (string) ($job->description ?: $job->short_description ?: ''),
            'responsibilities' => $this->paragraphLines((string) ($job->description ?: $job->requirements ?: 'Review the full job details for responsibilities.')),
            'requirements' => [
                [
                    'label' => 'Category',
                    'value' => $job->categories->pluck('name')->filter()->implode(', ') ?: 'Generalist',
                ],
                [
                    'label' => 'Core Skills',
                    'value' => $job->skills->pluck('name')->filter()->implode(', ') ?: 'Not specified',
                ],
                [
                    'label' => 'Experience',
                    'value' => $this->humanizeValue($job->experience_level?->value) ?? 'Not specified',
                ],
                [
                    'label' => 'Application',
                    'value' => $job->application_url ?: 'Apply through Larasearch',
                ],
            ],
            'summary_metrics' => [
                [
                    'label' => 'Compensation',
                    'value' => $this->salarySummary($job),
                ],
                [
                    'label' => 'Experience Required',
                    'value' => $this->humanizeValue($job->experience_level?->value) ?? 'Not specified',
                ],
                [
                    'label' => 'Work Location',
                    'value' => $primaryLocation ?? 'Remote',
                ],
                [
                    'label' => 'Job Type',
                    'value' => $this->humanizeValue($job->job_type?->value) ?? 'Not specified',
                ],
            ],
            'map_label' => $primaryLocation,
            'published_at' => $job->published_at?->toAtomString(),
            'highlight' => [
                'title' => null,
                'description' => null,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function relatedJobsPayload(JobListing $job): array
    {
        $categoryIds = $job->categories->pluck('id');
        $skillIds = $job->skills->pluck('id');

        return JobListing::query()
            ->with(['company', 'primaryLocation', 'skills'])
            ->whereKeyNot($job->getKey())
            ->visibleInSearch()
            ->when($categoryIds->isNotEmpty() || $skillIds->isNotEmpty(), function ($query) use ($categoryIds, $skillIds): void {
                $query->where(function ($query) use ($categoryIds, $skillIds): void {
                    if ($categoryIds->isNotEmpty()) {
                        $query->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->whereIn('categories.id', $categoryIds));
                    }

                    if ($skillIds->isNotEmpty()) {
                        $query->orWhereHas('skills', fn ($skillQuery) => $skillQuery->whereIn('skills.id', $skillIds));
                    }
                });
            }, fn ($query) => $query->where('company_id', $job->company_id))
            ->latest('published_at')
            ->limit(2)
            ->get()
            ->map(fn (JobListing $relatedJob): array => [
                'id' => $relatedJob->getKey(),
                'slug' => $relatedJob->slug,
                'title' => $relatedJob->title,
                'application_url' => $relatedJob->application_url,
                'company' => [
                    'name' => $relatedJob->company?->name,
                    'slug' => $relatedJob->company?->slug,
                    'website' => $relatedJob->company?->website_url,
                ],
                'locations' => $relatedJob->primaryLocation === null ? [] : [$relatedJob->primaryLocation->display_name],
                'skills' => $relatedJob->skills->pluck('name')->values()->all(),
                'salary' => [
                    'min' => $relatedJob->salary_min,
                    'max' => $relatedJob->salary_max,
                    'currency' => $relatedJob->salary_currency,
                    'is_visible' => $relatedJob->salary_is_visible,
                ],
                'job_type' => $relatedJob->job_type?->value,
                'work_model' => $relatedJob->work_model?->value,
                'experience_level' => $relatedJob->experience_level?->value,
                'primary_location' => $relatedJob->primaryLocation?->display_name,
                'published_at' => $relatedJob->published_at?->toAtomString(),
                'highlight' => [
                    'title' => null,
                    'description' => null,
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function paragraphLines(string $content): array
    {
        return collect(preg_split('/\n+/', $content) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->take(4)
            ->values()
            ->all();
    }

    protected function humanizeValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Str::of($value)
            ->replace('-', ' ')
            ->title()
            ->value();
    }

    protected function salarySummary(JobListing $job): string
    {
        if (! $job->salary_is_visible || ($job->salary_min === null && $job->salary_max === null)) {
            return 'Comp undisclosed';
        }

        $currency = $job->salary_currency ? "{$job->salary_currency} " : '';

        if ($job->salary_min !== null && $job->salary_max !== null) {
            return "{$currency}{$job->salary_min} - {$job->salary_max}";
        }

        return "{$currency}".($job->salary_min ?? $job->salary_max);
    }
}
