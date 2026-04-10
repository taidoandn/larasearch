<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Models\JobListing;
use Illuminate\Support\Str;

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
        $location = $this->normalizeLocation($params['location'] ?? null);

        $response = $this->client->search($this->alias(), [
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'sort' => $this->sortClause($sort),
            'query' => [
                'function_score' => [
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
                                $this->locationFilter($params['location'] ?? null, $location),
                                $this->termFilter('category_slugs', $params['category'] ?? null),
                                ...$this->allSkillsFilters($params['skills'] ?? null),
                                $this->termFilter('job_type', $params['job_type'] ?? null),
                                $this->termFilter('work_model', $params['work_model'] ?? null),
                                $this->termFilter('experience_level', $params['experience_level'] ?? null),
                                $this->minimumSalaryFilter($params['salary_min'] ?? null),
                                $this->maximumSalaryFilter($params['salary_max'] ?? null),
                            ])),
                        ],
                    ],
                    'functions' => [
                        [
                            'filter' => [
                                'term' => [
                                    'is_featured' => true,
                                ],
                            ],
                            'weight' => 1.5,
                        ],
                        [
                            'exp' => [
                                'published_at' => [
                                    'scale' => '7d',
                                    'decay' => 0.5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'locations' => $this->facetAggregation('location_slugs', 'location_labels'),
                'categories' => $this->facetAggregation('category_slugs', 'category_names'),
                'skills' => $this->facetAggregation('skill_slugs', 'skills'),
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
        $items = array_map(
            fn (array $hit): array => $this->normalizeItem($hit),
            data_get($response, 'hits.hits', []),
        );
        $visibleCount = count($items);
        $from = $visibleCount === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $to = $visibleCount === 0 ? 0 : $from + $visibleCount - 1;

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $from,
                'to' => $to,
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
            'id' => (int) ($source['id'] ?? 0),
            'slug' => (string) ($source['slug'] ?? ''),
            'title' => (string) ($source['title'] ?? ''),
            'description' => (string) ($source['short_description'] ?? $source['description'] ?? ''),
            'application_url' => $source['application_url'] ?? null,
            'company' => [
                'name' => $source['company_name'] ?? null,
                'slug' => $source['company_slug'] ?? null,
                'website' => $source['company_website'] ?? null,
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
            'locations' => $this->bucketFacet(
                $aggregations['locations']['buckets'] ?? [],
                'location_labels',
                'location_slugs',
            ),
            'categories' => $this->bucketFacet(
                $aggregations['categories']['buckets'] ?? [],
                'category_names',
                'category_slugs',
            ),
            'skills' => $this->bucketFacet(
                $aggregations['skills']['buckets'] ?? [],
                'skills',
                'skill_slugs',
            ),
            'job_types' => $this->bucketFacet($aggregations['job_types']['buckets'] ?? []),
            'work_models' => $this->bucketFacet($aggregations['work_models']['buckets'] ?? []),
            'experience_levels' => $this->bucketFacet($aggregations['experience_levels']['buckets'] ?? []),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $buckets
     * @return array<int, array<string, int|string>>
     */
    protected function bucketFacet(
        array $buckets,
        ?string $labelField = null,
        ?string $lookupField = null,
    ): array {
        return array_map(
            fn (array $bucket): array => [
                'value' => (string) ($bucket['key'] ?? ''),
                'label' => $this->firstFacetLabel($bucket, $labelField, $lookupField),
                'count' => (int) ($bucket['doc_count'] ?? 0),
            ],
            $buckets,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function facetAggregation(string $field, string $labelField): array
    {
        return [
            'terms' => [
                'field' => $field,
                'size' => 10,
            ],
            'aggs' => [
                'label' => [
                    'top_hits' => [
                        '_source' => [$field, $labelField],
                        'size' => 1,
                    ],
                ],
            ],
        ];
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
     * @return array<int, array<string, array<string, mixed>>>
     */
    protected function allSkillsFilters(mixed $values): array
    {
        if (! is_array($values) || $values === []) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $value): array => ['term' => ['skill_slugs' => (string) $value]],
            $values,
        ));
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
     * @return array<string, array<string, array<string, int>>>|null
     */
    protected function minimumSalaryFilter(mixed $minimum): ?array
    {
        if (! is_numeric($minimum)) {
            return null;
        }

        $minimumValue = (int) $minimum;

        return [
            'bool' => [
                'should' => [
                    $this->rangeFilter('salary_max', gte: $minimumValue),
                    [
                        'bool' => [
                            'must' => [
                                [
                                    'bool' => [
                                        'must_not' => [
                                            ['exists' => ['field' => 'salary_max']],
                                        ],
                                    ],
                                ],
                                $this->rangeFilter('salary_min', gte: $minimumValue),
                            ],
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @return array<string, array<string, array<string, int>>>|null
     */
    protected function maximumSalaryFilter(mixed $maximum): ?array
    {
        if (! is_numeric($maximum)) {
            return null;
        }

        $maximumValue = (int) $maximum;

        return [
            'bool' => [
                'should' => [
                    $this->rangeFilter('salary_min', lte: $maximumValue),
                    [
                        'bool' => [
                            'must' => [
                                [
                                    'bool' => [
                                        'must_not' => [
                                            ['exists' => ['field' => 'salary_min']],
                                        ],
                                    ],
                                ],
                                $this->rangeFilter('salary_max', lte: $maximumValue),
                            ],
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function locationFilter(mixed $rawLocation, ?string $normalizedLocation): ?array
    {
        if ($rawLocation === null || trim((string) $rawLocation) === '') {
            return null;
        }

        if ($normalizedLocation === null || $normalizedLocation === '') {
            return ['match_none' => (object) []];
        }

        $rawLocationValue = trim((string) $rawLocation);

        return [
            'bool' => [
                'should' => [
                    [
                        'term' => [
                            'location_slugs' => $normalizedLocation,
                        ],
                    ],
                    [
                        'wildcard' => [
                            'location_labels' => [
                                'value' => '*'.$rawLocationValue.'*',
                                'case_insensitive' => true,
                            ],
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    protected function normalizeLocation(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return Str::slug(trim((string) $value));
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

    protected function firstFacetLabel(
        array $bucket,
        ?string $labelField = null,
        ?string $lookupField = null,
    ): string {
        $source = (array) data_get($bucket, 'label.hits.hits.0._source', []);
        $bucketKey = (string) ($bucket['key'] ?? '');

        if ($labelField !== null) {
            $labels = data_get($source, $labelField);

            if (is_string($labels) && $labels !== '') {
                return $labels;
            }

            if (
                is_array($labels)
                && $labels !== []
                && $lookupField !== null
                && is_array(data_get($source, $lookupField))
            ) {
                /** @var array<int, mixed> $lookups */
                $lookups = data_get($source, $lookupField);

                foreach ($lookups as $index => $lookupValue) {
                    if ((string) $lookupValue !== $bucketKey) {
                        continue;
                    }

                    $matchedLabel = $labels[$index] ?? null;

                    if (is_string($matchedLabel) && $matchedLabel !== '') {
                        return $matchedLabel;
                    }
                }
            }

            if (is_array($labels) && $labels !== []) {
                $firstLabel = $labels[0] ?? null;

                if (is_string($firstLabel) && $firstLabel !== '') {
                    return $firstLabel;
                }
            }
        }

        foreach ($source as $value) {
            if (is_array($value) && $value !== []) {
                return (string) $value[0];
            }

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $bucketKey;
    }
}
