<?php

namespace App\Jobs;

use App\Models\JobListing;
use App\Search\Indexers\JobListingIndexer;
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

    public function handle(JobListingIndexer $indexer): void
    {
        if ($this->delete) {
            $indexer->delete($this->jobListingId);

            return;
        }

        $jobListing = JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->find($this->jobListingId);

        if ($jobListing === null) {
            return;
        }

        $indexer->indexJobListing($jobListing);
    }
}
