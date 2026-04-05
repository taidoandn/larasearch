<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchClient;
use Illuminate\Console\Command;

class ElasticsearchSwitchAliasCommand extends Command
{
    protected $signature = 'es:switch-alias {index} {--alias=}';

    protected $description = 'Switch the job listing alias to a target index.';

    public function handle(ElasticsearchClient $client): int
    {
        $index = (string) $this->argument('index');
        $alias = (string) ($this->option('alias') ?: config('elasticsearch.aliases.job_listings'));

        $client->updateAliases([
            [
                'remove' => [
                    'index' => '*',
                    'alias' => $alias,
                ],
            ],
            [
                'add' => [
                    'index' => $index,
                    'alias' => $alias,
                ],
            ],
        ]);

        $this->info("Alias [{$alias}] now points to [{$index}].");

        return self::SUCCESS;
    }
}
