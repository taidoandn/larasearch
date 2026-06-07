<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Search\Filters\JobListingFilters;
use App\Services\JobDetailPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobShowController extends Controller
{
    public function __invoke(Request $request, JobListing $job, JobDetailPresenter $presenter): Response
    {
        abort_unless($job->isVisibleInSearch(), 404);

        $job->loadMissing(['company', 'primaryLocation', 'categories', 'skills']);

        return Inertia::render('jobs/show', [
            'job' => $presenter->present($job),
            'relatedJobs' => $this->relatedJobsPayload($job, $presenter),
            'searchContext' => [
                'index_query' => JobListingFilters::compact($request->query()),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function relatedJobsPayload(JobListing $job, JobDetailPresenter $presenter): array
    {
        $categoryIds = $job->categories->pluck('id');
        $skillIds = $job->skills->pluck('id');

        return JobListing::query()
            ->select([
                'id',
                'company_id',
                'primary_location_id',
                'slug',
                'title',
                'application_url',
                'salary_min',
                'salary_max',
                'salary_currency',
                'salary_is_visible',
                'job_type',
                'work_model',
                'experience_level',
                'published_at',
                'is_active',
                'expires_at',
            ])
            ->with([
                'company:id,name,slug,logo_url,website_url',
                'primaryLocation:id,display_name',
                'skills:id,name',
            ])
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
            ->limit(3)
            ->get()
            ->map(fn (JobListing $relatedJob): array => $presenter->presentRelated($relatedJob))
            ->values()
            ->all();
    }
}
