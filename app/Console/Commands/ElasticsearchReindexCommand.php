<?php

namespace App\Console\Commands;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;
use App\Services\ElasticsearchClient;
use Illuminate\Console\Command;

class ElasticsearchReindexCommand extends Command
{
    protected $signature = 'es:reindex {index} {--chunk=250} {--alias=}';

    protected $description = 'Rebuild a versioned Elasticsearch index and switch the configured alias.';

    public function handle(ElasticsearchClient $client, SearchServiceInterface $searchService): int
    {
        $index = (string) $this->argument('index');
        $alias = (string) ($this->option('alias') ?: config('elasticsearch.aliases.job_listings'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $indexed = 0;

        $client->createIndex($index, config('elasticsearch.mapping', []));

        JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->chunkById($chunkSize, function ($jobListings) use ($searchService, $index, &$indexed): void {
                $indexed += $searchService->bulkIndexJobListings($jobListings, $index);
            });

        $client->refreshIndex($index);

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

        $this->info("Indexed {$indexed} job listings into [{$index}].");
        $this->info("Alias [{$alias}] now points to [{$index}].");

        return self::SUCCESS;
    }
}
