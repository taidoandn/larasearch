<?php

use App\Contracts\SearchServiceInterface;
use App\Jobs\SyncJobListingToElasticsearch;
use App\Models\Category;
use App\Models\JobListing;
use App\Models\Skill;
use Database\Seeders\JobMarketplaceSeeder;
use Illuminate\Support\Facades\Queue;

it('maps a job listing into the expected elasticsearch document shape', function () {
    $jobListing = JobListing::factory()->create([
        'slug' => 'staff-search-platform-engineer-sync-test',
        'title' => 'Staff Search Platform Engineer',
    ]);

    $categories = Category::factory()->count(2)->sequence(
        ['name' => 'Search Infrastructure', 'slug' => 'search-infrastructure'],
        ['name' => 'Platform Engineering', 'slug' => 'platform-engineering'],
    )->create();

    $skills = Skill::factory()->count(2)->sequence(
        ['name' => 'Elasticsearch', 'slug' => 'elasticsearch'],
        ['name' => 'Laravel', 'slug' => 'laravel'],
    )->create();

    $jobListing->categories()->sync($categories->pluck('id')->all());
    $jobListing->skills()->sync([
        $skills[0]->id => ['is_primary' => true, 'weight' => 3],
        $skills[1]->id => ['is_primary' => false, 'weight' => 2],
    ]);

    $document = $jobListing->fresh()->toSearchDocument();

    expect($document['id'])->toBe((string) $jobListing->id)
        ->and($document['slug'])->toBe('staff-search-platform-engineer-sync-test')
        ->and($document['title'])->toBe('Staff Search Platform Engineer')
        ->and($document['company_name'])->toBe($jobListing->company->name)
        ->and($document['locations'])->toBe([$jobListing->primaryLocation->display_name])
        ->and(collect($document['category_names'])->sort()->values()->all())->toBe(['Platform Engineering', 'Search Infrastructure'])
        ->and(collect($document['skills'])->sort()->values()->all())->toBe(['elasticsearch', 'laravel']);
});

it('sync job indexes the loaded job listing document', function () {
    $jobListing = JobListing::factory()->create();

    $searchService = Mockery::mock(SearchServiceInterface::class);
    $searchService->shouldReceive('indexJobListing')
        ->once()
        ->with(Mockery::on(fn (JobListing $listedJob): bool => $listedJob->is($jobListing)));

    app()->instance(SearchServiceInterface::class, $searchService);

    app(SyncJobListingToElasticsearch::class, ['jobListingId' => $jobListing->id])->handle($searchService);
});

it('sync job deletes the search document when requested', function () {
    $searchService = Mockery::mock(SearchServiceInterface::class);
    $searchService->shouldReceive('deleteJobListing')->once()->with(1234);

    app()->instance(SearchServiceInterface::class, $searchService);

    app(SyncJobListingToElasticsearch::class, ['jobListingId' => 1234, 'delete' => true])->handle($searchService);
});

it('seeds the marketplace without queueing search sync jobs', function () {
    Queue::fake();

    putenv('JOB_MARKETPLACE_SEED_COUNT=2');
    $_ENV['JOB_MARKETPLACE_SEED_COUNT'] = '2';
    $_SERVER['JOB_MARKETPLACE_SEED_COUNT'] = '2';

    $this->seed(JobMarketplaceSeeder::class);

    Queue::assertNothingPushed();

    putenv('JOB_MARKETPLACE_SEED_COUNT');
    unset($_ENV['JOB_MARKETPLACE_SEED_COUNT'], $_SERVER['JOB_MARKETPLACE_SEED_COUNT']);
});
