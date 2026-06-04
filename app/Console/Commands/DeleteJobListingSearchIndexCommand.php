<?php

namespace App\Console\Commands;

use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class DeleteJobListingSearchIndexCommand extends Command
{
    protected $signature = 'es:job-listings:delete-index {index?}';

    protected $description = 'Delete the configured Elasticsearch job listing index.';

    public function handle(JobListingIndexer $indexer): int
    {
        $index = $indexer->deleteIndex((string) ($this->argument('index') ?: ''));

        $this->info("Deleted index [{$index}].");

        return self::SUCCESS;
    }
}
