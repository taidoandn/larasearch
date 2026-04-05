<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchClient;
use Illuminate\Console\Command;

class ElasticsearchCreateIndexCommand extends Command
{
    protected $signature = 'es:create-index {index?}';

    protected $description = 'Create the configured Elasticsearch index.';

    public function handle(ElasticsearchClient $client): int
    {
        $index = (string) ($this->argument('index') ?: config('elasticsearch.indexes.job_listings'));

        $client->createIndex($index, config('elasticsearch.mapping'));

        $this->info("Created index [{$index}].");

        return self::SUCCESS;
    }
}
