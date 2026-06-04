<?php

namespace App\Console\Commands;

use App\Search\Indexers\JobListingIndexer;
use Illuminate\Console\Command;

class SwitchJobListingSearchAliasCommand extends Command
{
    protected $signature = 'es:job-listings:switch-alias {index} {--alias=}';

    protected $description = 'Switch the job listing alias to a target index.';

    public function handle(JobListingIndexer $indexer): int
    {
        $index = (string) $this->argument('index');
        $alias = (string) ($this->option('alias') ?: config('elasticsearch.aliases.job_listings'));
        $indexer->switchAlias($index, $alias);

        $this->info("Alias [{$alias}] now points to [{$index}].");

        return self::SUCCESS;
    }
}
