<?php

use App\Jobs\SyncJobListingToElasticsearch;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

it('dispatches a sync job after a job listing is created', function () {
    Queue::fake();

    $jobListing = JobListing::factory()->create();

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing): bool {
        return $job->jobListingId === $jobListing->id
            && $job->delete === false
            && $job->afterCommit === true;
    });
});

it('dispatches the sync job only after the surrounding transaction commits', function () {
    if (config('database.default') === 'sqlite') {
        $this->markTestSkipped('afterCommit queue timing is not reliable on the sqlite test connection.');
    }

    Queue::fake();

    $jobListing = null;

    DB::transaction(function () use (&$jobListing): void {
        $jobListing = JobListing::factory()->create();

        Queue::assertNothingPushed();
    });

    expect($jobListing)->toBeInstanceOf(JobListing::class);

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing): bool {
        return $job->jobListingId === $jobListing->id
            && $job->delete === false
            && $job->afterCommit === true;
    });
});

it('dispatches a sync job after a job listing is updated', function () {
    Queue::fake();

    $jobListing = JobListing::factory()->create();

    Queue::clearResolvedInstances();
    Queue::fake();

    $jobListing->update([
        'title' => 'Updated Search Platform Engineer',
    ]);

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing): bool {
        return $job->jobListingId === $jobListing->id
            && $job->delete === false
            && $job->afterCommit === true;
    });
});

it('dispatches a delete sync job after a job listing is deleted', function () {
    Queue::fake();

    $jobListing = JobListing::factory()->create();

    Queue::clearResolvedInstances();
    Queue::fake();

    $jobListing->delete();

    Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing): bool {
        return $job->jobListingId === $jobListing->id
            && $job->delete === true
            && $job->afterCommit === true;
    });
});

it('dispatches delete sync jobs for job listings before a company cascade delete', function () {
    Queue::fake();

    $company = Company::factory()->create();
    $jobListings = JobListing::factory()->count(2)->for($company)->create();

    Queue::clearResolvedInstances();
    Queue::fake();

    $company->delete();

    foreach ($jobListings as $jobListing) {
        Queue::assertPushed(SyncJobListingToElasticsearch::class, function (SyncJobListingToElasticsearch $job) use ($jobListing): bool {
            return $job->jobListingId === $jobListing->id
                && $job->delete === true
                && $job->afterCommit === true;
        });
    }
});
