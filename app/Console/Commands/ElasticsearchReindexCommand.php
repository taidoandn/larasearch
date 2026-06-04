<?php

namespace App\Console\Commands;

use App\Indexers\JobListingIndexer;
use App\Models\JobListing;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class ElasticsearchReindexCommand extends Command
{
    protected $signature = 'es:reindex {index} {--chunk=250} {--alias=}';

    protected $description = 'Rebuild a versioned Elasticsearch index and switch the configured alias.';

    public function handle(Client $client, JobListingIndexer $indexer): int
    {
        $index = (string) $this->argument('index');
        $alias = (string) ($this->option('alias') ?: config('elasticsearch.aliases.job_listings'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $indexed = 0;

        $client->indices()->create([
            'index' => $index,
            'body' => config('elasticsearch.mapping', []),
        ])->asArray();

        JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->chunkById($chunkSize, function ($jobListings) use ($indexer, $index, &$indexed): void {
                $indexed += $indexer->bulkIndex($jobListings, $index);
            });

        $client->indices()->refresh([
            'index' => $index,
        ])->asArray();

        $client->indices()->updateAliases([
            'body' => [
                'actions' => [
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
                ],
            ],
        ])->asArray();

        $this->info("Indexed {$indexed} job listings into [{$index}].");
        $this->info("Alias [{$alias}] now points to [{$index}].");

        return self::SUCCESS;
    }
}
