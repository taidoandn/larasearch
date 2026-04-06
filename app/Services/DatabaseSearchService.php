<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;

class DatabaseSearchService implements SearchServiceInterface
{
    public function search(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($params['per_page'] ?? 20)));

        return [
            'items' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 0,
                'has_more' => false,
            ],
            'facets' => [
                'locations' => [],
                'categories' => [],
                'skills' => [],
                'job_types' => [],
                'work_models' => [],
                'experience_levels' => [],
            ],
            'sort' => (string) ($params['sort'] ?? 'best_match'),
        ];
    }

    public function indexJobListing(JobListing $jobListing): void {}

    public function deleteJobListing(int $jobListingId): void {}

    public function bulkIndexJobListings(iterable $jobListings, ?string $target = null): int
    {
        return collect($jobListings)->count();
    }
}
