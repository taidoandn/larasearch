<?php

use App\Indexers\JobListingIndexer;
use App\Models\JobListing;
use Illuminate\Support\Facades\Queue;

it('indexes job listings in chunks', function () {
    // JobListing factories trigger observer-based sync jobs; fake the queue so command assertions stay isolated.
    Queue::fake();

    JobListing::factory()->count(3)->create();

    $searchService = Mockery::mock(JobListingIndexer::class);
    $searchService->shouldReceive('bulkIndex')
        ->twice()
        ->andReturnUsing(fn ($jobListings, $target = null): int => $jobListings->count());

    app()->instance(JobListingIndexer::class, $searchService);

    $this->artisan('es:index-job-listings --chunk=2')
        ->expectsOutputToContain('Indexed 3 job listings to [job_listings_current].')
        ->assertExitCode(0);
});

it('indexes job listings to an explicit versioned index', function () {
    // Prevent observer side effects during setup; this test only cares about bulk indexing command behavior.
    Queue::fake();

    JobListing::factory()->count(3)->create();

    $searchService = Mockery::mock(JobListingIndexer::class);
    $searchService->shouldReceive('bulkIndex')
        ->twice()
        ->withArgs(fn ($jobListings, $target): bool => $jobListings->count() > 0 && $target === 'job_listings_v2')
        ->andReturnUsing(fn ($jobListings, $target = null): int => $jobListings->count());

    app()->instance(JobListingIndexer::class, $searchService);

    $this->artisan('es:index-job-listings --chunk=2 --index=job_listings_v2')
        ->expectsOutputToContain('Indexed 3 job listings to [job_listings_v2].')
        ->assertExitCode(0);
});
