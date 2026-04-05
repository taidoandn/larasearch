<?php

namespace App\Contracts;

use App\Models\JobListing;

interface SearchServiceInterface
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function search(array $params): array;

    public function indexJobListing(JobListing $jobListing): void;

    public function deleteJobListing(int $jobListingId): void;

    /**
     * @param  iterable<JobListing>  $jobListings
     */
    public function bulkIndexJobListings(iterable $jobListings, ?string $target = null): int;
}
