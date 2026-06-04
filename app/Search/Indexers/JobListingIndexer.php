<?php

namespace App\Search\Indexers;

use App\Models\JobListing;
use Illuminate\Support\Collection;
use RuntimeException;

class JobListingIndexer extends BaseIndexer
{
    public function index(JobListing $jobListing): void
    {
        $this->client->index([
            'index' => $this->alias(),
            'id' => (string) $jobListing->getKey(),
            'body' => $jobListing->toSearchDocument(),
        ])->asArray();
    }

    public function indexJobListing(JobListing $jobListing): void
    {
        $this->index($jobListing);
    }

    public function delete(int $jobListingId): void
    {
        $this->client->delete([
            'index' => $this->alias(),
            'id' => (string) $jobListingId,
        ])->asArray();
    }

    /**
     * @param  iterable<int, JobListing>  $jobListings
     */
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

        if ($operations === []) {
            return 0;
        }

        $response = $this->client->bulk([
            'body' => $operations,
        ])->asArray();

        if (($response['errors'] ?? false) === true) {
            throw new RuntimeException('Elasticsearch bulk indexing completed with item errors.');
        }

        return $count;
    }

    public function reindex(string $index, int $chunkSize = 250, ?string $alias = null): int
    {
        $indexed = 0;

        $this->createIndex($index);

        JobListing::query()
            ->with(['company', 'primaryLocation', 'categories', 'skills'])
            ->chunkById($chunkSize, function (Collection $jobListings) use ($index, &$indexed): void {
                $indexed += $this->bulkIndex($jobListings, $index);
            });

        $this->refresh($index);
        $this->switchAlias($index, $alias);

        return $indexed;
    }

    public function alias(): string
    {
        return (string) config('elasticsearch.aliases.job_listings');
    }

    public function configuredIndex(): string
    {
        return (string) config('elasticsearch.indexes.job_listings');
    }

    public function indexPrefix(): string
    {
        return (string) config('elasticsearch.index_prefixes.job_listings', 'job_listings_');
    }

    /**
     * @return array<string, mixed>
     */
    public function mapping(): array
    {
        return (array) config('elasticsearch.indices.job_listings', config('elasticsearch.mapping', []));
    }
}
