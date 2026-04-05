<?php

namespace App\Console\Commands;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;
use Illuminate\Console\Command;

class ElasticsearchIndexJobListingsCommand extends Command
{
    protected $signature = 'es:index-job-listings {--chunk=250} {--index=}';

    protected $description = 'Bulk index job listings to Elasticsearch in chunks.';

    public function handle(SearchServiceInterface $searchService): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $target = $this->option('index');
        $indexed = 0;

        JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->chunkById($chunkSize, function ($jobListings) use ($searchService, $target, &$indexed): void {
                $indexed += $searchService->bulkIndexJobListings($jobListings, $target);
            });

        $destination = $target ?: config('elasticsearch.aliases.job_listings');

        $this->info("Indexed {$indexed} job listings to [{$destination}].");

        return self::SUCCESS;
    }
}
