<?php

namespace App\Console\Commands;

use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class CleanupOldJobListingSearchIndicesCommand extends Command
{
    protected $signature = 'es:job-listings:cleanup-old-indices {--keep=}';

    protected $description = 'Delete old inactive versioned Elasticsearch job listing indices.';

    public function handle(JobListingIndexer $indexer): int
    {
        $keep = $this->option('keep') === null
            ? (int) config('elasticsearch.cleanup.keep', 2)
            : max(0, (int) $this->option('keep'));

        $deletedIndices = $indexer->cleanupOldIndices($keep);

        if ($deletedIndices === []) {
            $this->info('No old indices to delete.');

            return self::SUCCESS;
        }

        foreach ($deletedIndices as $index) {
            $this->info("Deleted old index [{$index}].");
        }

        return self::SUCCESS;
    }
}
