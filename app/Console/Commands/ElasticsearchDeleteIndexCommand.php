<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchClient;
use Illuminate\Console\Command;

class ElasticsearchDeleteIndexCommand extends Command
{
    protected $signature = 'es:delete-index {index?}';

    protected $description = 'Delete the configured Elasticsearch index.';

    public function handle(ElasticsearchClient $client): int
    {
        $index = (string) ($this->argument('index') ?: config('elasticsearch.indexes.job_listings'));

        $client->deleteIndex($index);

        $this->info("Deleted index [{$index}].");

        return self::SUCCESS;
    }
}
