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
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($params['per_page'] ?? 20)));
        $sort = (string) ($params['sort'] ?? 'best_match');
        $response = $this->client->search($this->alias(), [
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'sort' => $this->sortClause($sort),
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
                        ['term' => ['is_active' => true]],
                        ['range' => ['published_at' => ['lte' => 'now']]],
                        [
                            'bool' => [
                                'should' => [
                                    ['bool' => ['must_not' => [['exists' => ['field' => 'expires_at']]]]],
                                    ['range' => ['expires_at' => ['gt' => 'now']]],
                                ],
                                'minimum_should_match' => 1,
                            ],
                        ],
                        $this->termFilter('locations', $params['location'] ?? null),
                        $this->termFilter('category_names', $params['category'] ?? null),
                        $this->termsFilter('skills', $params['skills'] ?? null),
                        $this->termFilter('job_type', $params['job_type'] ?? null),
                        $this->termFilter('work_model', $params['work_model'] ?? null),
                        $this->termFilter('experience_level', $params['experience_level'] ?? null),
                        $this->rangeFilter('salary_min', gte: $params['salary_min'] ?? null),
                        $this->rangeFilter('salary_max', lte: $params['salary_max'] ?? null),
                    ])),
                ],
            ],
            'aggs' => [
                'locations' => ['terms' => ['field' => 'locations', 'size' => 10]],
                'categories' => ['terms' => ['field' => 'category_names', 'size' => 10]],
                'skills' => ['terms' => ['field' => 'skills', 'size' => 10]],
                'job_types' => ['terms' => ['field' => 'job_type', 'size' => 10]],
                'work_models' => ['terms' => ['field' => 'work_model', 'size' => 10]],
                'experience_levels' => ['terms' => ['field' => 'experience_level', 'size' => 10]],
            ],
            'highlight' => [
                'fields' => [
                    'title' => (object) [],
                    'description' => (object) [],
                ],
            ],
        ]);

        return $this->normalizeResults($response, $page, $perPage, $sort);
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
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    protected function normalizeResults(array $response, int $page, int $perPage, string $sort): array
    {
        $total = (int) data_get($response, 'hits.total.value', 0);

        return [
            'items' => array_map(
                fn (array $hit): array => $this->normalizeItem($hit),
                data_get($response, 'hits.hits', []),
            ),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $total === 0 ? 0 : (int) ceil($total / $perPage),
                'has_more' => ($page * $perPage) < $total,
            ],
            'facets' => $this->normalizeFacets((array) ($response['aggregations'] ?? [])),
            'sort' => $sort,
        ];
    }

    /**
     * @param  array<string, mixed>  $hit
     * @return array<string, mixed>
     */
    protected function normalizeItem(array $hit): array
    {
        /** @var array<string, mixed> $source */
        $source = (array) ($hit['_source'] ?? []);
        $locationLabels = $this->stringList($source['location_labels'] ?? $source['locations'] ?? []);

        return [
            'id' => (string) ($source['id'] ?? ''),
            'slug' => (string) ($source['slug'] ?? ''),
            'title' => (string) ($source['title'] ?? ''),
            'company' => [
                'name' => $source['company_name'] ?? null,
                'slug' => $source['company_slug'] ?? null,
            ],
            'primary_location' => $locationLabels[0] ?? null,
            'locations' => $locationLabels,
            'skills' => $this->stringList($source['skills'] ?? []),
            'salary' => [
                'min' => $source['salary_min'] ?? null,
                'max' => $source['salary_max'] ?? null,
                'currency' => $source['salary_currency'] ?? null,
                'is_visible' => (bool) ($source['salary_is_visible'] ?? false),
            ],
            'job_type' => $source['job_type'] ?? null,
            'work_model' => $source['work_model'] ?? null,
            'experience_level' => $source['experience_level'] ?? null,
            'published_at' => $source['published_at'] ?? null,
            'highlight' => [
                'title' => $this->firstString(data_get($hit, 'highlight.title', [])),
                'description' => $this->firstString(data_get($hit, 'highlight.description', [])),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @return array<string, array<int, array<string, int|string>>>
     */
    protected function normalizeFacets(array $aggregations): array
    {
        return [
            'locations' => $this->bucketFacet($aggregations['locations']['buckets'] ?? []),
            'categories' => $this->bucketFacet($aggregations['categories']['buckets'] ?? []),
            'skills' => $this->bucketFacet($aggregations['skills']['buckets'] ?? []),
            'job_types' => $this->bucketFacet($aggregations['job_types']['buckets'] ?? []),
            'work_models' => $this->bucketFacet($aggregations['work_models']['buckets'] ?? []),
            'experience_levels' => $this->bucketFacet($aggregations['experience_levels']['buckets'] ?? []),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $buckets
     * @return array<int, array<string, int|string>>
     */
    protected function bucketFacet(array $buckets): array
    {
        return array_map(
            fn (array $bucket): array => [
                'value' => (string) ($bucket['key'] ?? ''),
                'count' => (int) ($bucket['doc_count'] ?? 0),
            ],
            $buckets,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function sortClause(string $sort): array
    {
        return match ($sort) {
            'newest' => [
                ['published_at' => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ],
            'salary_desc' => [
                ['salary_max' => ['order' => 'desc', 'missing' => '_last']],
                ['id' => ['order' => 'desc']],
            ],
            'salary_asc' => [
                ['salary_min' => ['order' => 'asc', 'missing' => '_last']],
                ['id' => ['order' => 'desc']],
            ],
            default => [
                ['_score' => ['order' => 'desc']],
                ['published_at' => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ],
        };
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

    /**
     * @return array<string, array<string, mixed>>|null
     */
    protected function termsFilter(string $field, mixed $values): ?array
    {
        if (! is_array($values) || $values === []) {
            return null;
        }

        return [
            'terms' => [
                $field => array_values($values),
            ],
        ];
    }

    /**
     * @return array<string, array<string, array<string, int>>>|null
     */
    protected function rangeFilter(string $field, mixed $gte = null, mixed $lte = null): ?array
    {
        $range = array_filter([
            'gte' => is_numeric($gte) ? (int) $gte : null,
            'lte' => is_numeric($lte) ? (int) $lte : null,
        ], fn (mixed $value): bool => $value !== null);

        if ($range === []) {
            return null;
        }

        return [
            'range' => [
                $field => $range,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $value): string => (string) $value, $values));
    }

    protected function firstString(mixed $values): ?string
    {
        if (! is_array($values) || $values === []) {
            return null;
        }

        return (string) $values[0];
    }
}
