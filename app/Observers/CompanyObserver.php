<?php

namespace App\Observers;

use App\Jobs\SyncJobListingToElasticsearch;
use App\Models\Company;

class CompanyObserver
{
    public function deleting(Company $company): void
    {
        $company->jobListings()
            ->pluck('job_listings.id')
            ->each(function (int $jobListingId): void {
                SyncJobListingToElasticsearch::dispatch($jobListingId, true);
            });
    }
}
