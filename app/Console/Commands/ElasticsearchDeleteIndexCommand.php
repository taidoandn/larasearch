<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchDeleteIndexCommand extends Command
{
    protected $signature = 'es:delete-index {index?}';

    protected $description = 'Delete the configured Elasticsearch index.';

    public function handle(Client $client): int
    {
        $index = (string) ($this->argument('index') ?: config('elasticsearch.indexes.job_listings'));

        $client->indices()->delete([
            'index' => $index,
        ])->asArray();

        $this->info("Deleted index [{$index}].");

        return self::SUCCESS;
    }
}
