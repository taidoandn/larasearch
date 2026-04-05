<?php

namespace App\Observers;

use App\Jobs\SyncJobListingToElasticsearch;
use App\Models\JobListing;

class JobListingObserver
{
    public function created(JobListing $jobListing): void
    {
        SyncJobListingToElasticsearch::dispatch($jobListing->getKey());
    }

    public function updated(JobListing $jobListing): void
    {
        SyncJobListingToElasticsearch::dispatch($jobListing->getKey());
    }

    public function deleted(JobListing $jobListing): void
    {
        SyncJobListingToElasticsearch::dispatch($jobListing->getKey(), true);
    }
}
