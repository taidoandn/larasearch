<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchClient;
use Illuminate\Console\Command;

class ElasticsearchHealthCommand extends Command
{
    protected $signature = 'es:health';

    protected $description = 'Show Elasticsearch health and configured index aliases.';

    public function handle(ElasticsearchClient $client): int
    {
        $health = $client->health();

        $this->info('Elasticsearch is reachable.');
        $this->line('Cluster: '.($health['cluster_name'] ?? 'unknown'));
        $this->line('Status: '.($health['status'] ?? 'unknown'));
        $this->line('Alias: '.config('elasticsearch.aliases.job_listings'));

        return self::SUCCESS;
    }
}
