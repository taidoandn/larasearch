<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchCreateIndexCommand extends Command
{
    protected $signature = 'es:create-index {index?}';

    protected $description = 'Create the configured Elasticsearch index.';

    public function handle(Client $client): int
    {
        $index = (string) ($this->argument('index') ?: config('elasticsearch.indexes.job_listings'));

        $client->indices()->create([
            'index' => $index,
            'body' => config('elasticsearch.mapping'),
        ])->asArray();

        $this->info("Created index [{$index}].");

        return self::SUCCESS;
    }
}
