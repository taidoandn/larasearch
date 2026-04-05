<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;

class DatabaseSearchService implements SearchServiceInterface
{
    public function search(array $params): array
    {
        return [
            'hits' => [
                'total' => [
                    'value' => 0,
                ],
                'hits' => [],
            ],
            'params' => $params,
        ];
    }

    public function indexJobListing(JobListing $jobListing): void {}

    public function deleteJobListing(int $jobListingId): void {}

    public function bulkIndexJobListings(iterable $jobListings, ?string $target = null): int
    {
        return collect($jobListings)->count();
    }
}
