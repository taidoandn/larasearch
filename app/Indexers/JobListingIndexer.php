<?php

namespace App\Indexers;

use App\Models\JobListing;
use Elastic\Elasticsearch\Client;

class JobListingIndexer
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function index(JobListing $jobListing): void
    {
        $this->client->index([
            'index' => $this->alias(),
            'id' => (string) $jobListing->getKey(),
            'body' => $jobListing->toSearchDocument(),
        ])->asArray();
    }

    public function delete(int $jobListingId): void
    {
        $this->client->delete([
            'index' => $this->alias(),
            'id' => (string) $jobListingId,
        ])->asArray();
    }

    public function bulkIndex(iterable $jobListings, ?string $target = null): int
    {
        $operations = [];
        $count = 0;
        $index = $target ?? $this->alias();

        foreach ($jobListings as $jobListing) {
            $operations[] = [
                'index' => [
                    '_index' => $index,
                    '_id' => $jobListing->getKey(),
                ],
            ];
            $operations[] = $jobListing->toSearchDocument();
            $count++;
        }

        if ($operations !== []) {
            $this->client->bulk([
                'body' => $operations,
            ])->asArray();
        }

        return $count;
    }

    public function alias(): string
    {
        return (string) config('elasticsearch.aliases.job_listings');
    }
}
