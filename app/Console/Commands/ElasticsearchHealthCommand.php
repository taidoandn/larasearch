<?php

namespace App\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchHealthCommand extends Command
{
    protected $signature = 'es:health';

    protected $description = 'Show Elasticsearch health and configured index aliases.';

    public function handle(Client $client): int
    {
        $health = $client->cluster()->health()->asArray();

        $this->info('Elasticsearch is reachable.');
        $this->line('Cluster: '.($health['cluster_name'] ?? 'unknown'));
        $this->line('Status: '.($health['status'] ?? 'unknown'));
        $this->line('Job listings alias: '.config('elasticsearch.aliases.job_listings'));

        return self::SUCCESS;
    }
}
