<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;

class ElasticsearchSearchService implements SearchServiceInterface
{
    public function __construct(
        private readonly ElasticsearchClient $client,
    ) {}

    public function search(array $params): array
    {
        $query = trim((string) ($params['q'] ?? ''));

        return $this->client->search($this->alias(), [
            'query' => [
                'bool' => [
                    'must' => $query === ''
                        ? [['match_all' => (object) []]]
                        : [[
                            'multi_match' => [
                                'query' => $query,
                                'fields' => [
                                    'title^3',
                                    'skills_text^2',
                                    'company_name^2',
                                    'description',
                                ],
                            ],
                        ]],
                    'filter' => array_values(array_filter([
                        $this->termFilter('locations', $params['location'] ?? null),
                        $this->termFilter('job_type', $params['job_type'] ?? null),
                        $this->termFilter('work_model', $params['work_model'] ?? null),
                        $this->termFilter('experience_level', $params['experience_level'] ?? null),
                    ])),
                ],
            ],
        ]);
    }

    public function indexJobListing(JobListing $jobListing): void
    {
        $this->client->indexDocument($this->alias(), $jobListing->getKey(), $jobListing->toSearchDocument());
    }

    public function deleteJobListing(int $jobListingId): void
    {
        $this->client->deleteDocument($this->alias(), $jobListingId);
    }

    public function bulkIndexJobListings(iterable $jobListings, ?string $target = null): int
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
            $this->client->bulk($operations);
        }

        return $count;
    }

    protected function alias(): string
    {
        return (string) config('elasticsearch.aliases.job_listings');
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    protected function termFilter(string $field, mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        return [
            'term' => [
                $field => $value,
            ],
        ];
    }
}
