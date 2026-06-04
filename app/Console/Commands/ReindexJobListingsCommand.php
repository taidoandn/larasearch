<?php

namespace App\Console\Commands;

use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class ReindexJobListingsCommand extends Command
{
    protected $signature = 'es:job-listings:reindex {index} {--chunk=250} {--alias=}';

    protected $description = 'Rebuild a versioned Elasticsearch job listing index and switch the configured alias.';

    public function handle(JobListingIndexer $indexer): int
    {
        $index = (string) $this->argument('index');
        $alias = (string) ($this->option('alias') ?: config('elasticsearch.aliases.job_listings'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $indexed = $indexer->reindex($index, $chunkSize, $alias);

        $this->info("Indexed {$indexed} job listings into [{$index}].");
        $this->info("Alias [{$alias}] now points to [{$index}].");

        return self::SUCCESS;
    }
}
