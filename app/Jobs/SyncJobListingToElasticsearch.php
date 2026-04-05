<?php

namespace App\Jobs;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncJobListingToElasticsearch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $jobListingId,
        public bool $delete = false,
    ) {
        $this->afterCommit();
    }

    public function handle(SearchServiceInterface $searchService): void
    {
        if ($this->delete) {
            $searchService->deleteJobListing($this->jobListingId);

            return;
        }

        $jobListing = JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->find($this->jobListingId);

        if ($jobListing === null) {
            return;
        }

        $searchService->indexJobListing($jobListing);
    }
}
