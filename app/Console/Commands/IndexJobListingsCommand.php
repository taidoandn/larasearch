<?php

namespace App\Console\Commands;

use App\Models\JobListing;
use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class IndexJobListingsCommand extends Command
{
    protected $signature = 'es:job-listings:index {--chunk=250} {--index=}';

    protected $description = 'Bulk index job listings to Elasticsearch in chunks.';

    public function handle(JobListingIndexer $indexer): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $target = $this->option('index');
        $indexed = 0;

        JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->chunkById($chunkSize, function ($jobListings) use ($indexer, $target, &$indexed): void {
                $indexed += $indexer->bulkIndex($jobListings, $target);
            });

        $destination = $target ?: config('elasticsearch.aliases.job_listings');

        $this->info("Indexed {$indexed} job listings to [{$destination}].");

        return self::SUCCESS;
    }
}
