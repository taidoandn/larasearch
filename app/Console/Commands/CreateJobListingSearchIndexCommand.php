<?php

namespace App\Console\Commands;

use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class CreateJobListingSearchIndexCommand extends Command
{
    protected $signature = 'es:job-listings:create-index {index?}';

    protected $description = 'Create the configured Elasticsearch job listing index.';

    public function handle(JobListingIndexer $indexer): int
    {
        $index = $indexer->createIndex((string) ($this->argument('index') ?: ''));

        $this->info("Created index [{$index}].");

        return self::SUCCESS;
    }
}
