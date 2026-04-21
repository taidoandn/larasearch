<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Models\JobListing;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ElasticsearchSearchService implements SearchServiceInterface
{
    protected const int FACET_BUCKET_LIMIT = 1000;

    public function __construct(
        private readonly ElasticsearchClient $client,
    ) {}

    public function search(array $params): array
    {
        $query = trim((string) ($params['q'] ?? ''));
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($params['per_page'] ?? 20)));
        $sort = (string) ($params['sort'] ?? 'best_match');
        $locations = $this->normalizeLocations($params['location'] ?? null);
        $visibilityFilters = $this->visibilityFilters();
        $salaryFilters = $this->salaryFilters($params);
        $skillFilters = $this->skillFilters($params);
        $locationFilter = $this->locationFilter($locations);
        $categoryFilter = $this->anyTermsFilter('category_slugs', $params['category'] ?? null);
        $jobTypeFilter = $this->anyTermsFilter('job_type', $params['job_type'] ?? null);
        $workModelFilter = $this->anyTermsFilter('work_model', $params['work_model'] ?? null);
        $experienceLevelFilter = $this->anyTermsFilter('experience_level', $params['experience_level'] ?? null);
        $resultFilters = array_values(array_filter([
            ...$visibilityFilters,
            ...$salaryFilters,
            ...$skillFilters,
            $locationFilter,
            $categoryFilter,
            $jobTypeFilter,
            $workModelFilter,
            $experienceLevelFilter,
        ]));

        $response = $this->client->search($this->alias(), [
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'sort' => $this->sortClause($sort),
            'query' => $this->searchQuery($query, $sort, $resultFilters),
            'aggs' => [
                'locations' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $categoryFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                    $this->facetAggregation('location_slugs', 'location_labels'),
                ),
                'categories' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                    $this->facetAggregation('category_slugs', 'category_names'),
                ),
                'skills' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                    $this->facetAggregation('skill_slugs', 'skills'),
                ),
                'job_types' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $workModelFilter, $experienceLevelFilter],
                    $this->termsAggregation('job_type'),
                ),
                'work_models' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $experienceLevelFilter],
                    $this->termsAggregation('work_model'),
                ),
                'experience_levels' => $this->scopedFacetAggregation(
                    $query,
                    [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $workModelFilter],
                    $this->termsAggregation('experience_level'),
                ),
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
     * @param  array<int, array<string, mixed>>  $baseFilters
     * @return array<string, mixed>
     */
    protected function searchQuery(string $query, string $sort, array $baseFilters): array
    {
        $baseQuery = [
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
                'filter' => $baseFilters,
            ],
        ];

        if ($sort !== 'best_match') {
            return $baseQuery;
        }

        return [
            'function_score' => [
                'query' => $baseQuery,
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
        ];
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
                'logo_url' => $source['company_logo_url'] ?? null,
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
            'job_type_label' => JobType::tryFrom((string) ($source['job_type'] ?? ''))?->label(),
            'work_model' => $source['work_model'] ?? null,
            'work_model_label' => WorkModel::tryFrom((string) ($source['work_model'] ?? ''))?->label(),
            'experience_level' => $source['experience_level'] ?? null,
            'experience_level_label' => ExperienceLevel::tryFrom((string) ($source['experience_level'] ?? ''))?->label(),
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
                $this->facetBuckets($aggregations, 'locations'),
                'location_labels',
                'location_slugs',
            ),
            'categories' => $this->bucketFacet(
                $this->facetBuckets($aggregations, 'categories'),
                'category_names',
                'category_slugs',
            ),
            'skills' => $this->bucketFacet(
                $this->facetBuckets($aggregations, 'skills'),
                'skills',
                'skill_slugs',
            ),
            'job_types' => $this->bucketFacet($this->facetBuckets($aggregations, 'job_types')),
            'work_models' => $this->bucketFacet($this->facetBuckets($aggregations, 'work_models')),
            'experience_levels' => $this->bucketFacet($this->facetBuckets($aggregations, 'experience_levels')),
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
                'size' => self::FACET_BUCKET_LIMIT,
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
     * @return array<string, mixed>
     */
    protected function termsAggregation(string $field): array
    {
        return [
            'terms' => [
                'field' => $field,
                'size' => self::FACET_BUCKET_LIMIT,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $filters
     * @param  array<string, mixed>  $aggregation
     * @return array<string, mixed>
     */
    protected function scopedFacetAggregation(string $query, array $filters, array $aggregation): array
    {
        return [
            'global' => (object) [],
            'aggs' => [
                'scope' => [
                    'filter' => $this->facetScopeQuery($query, $filters),
                    'aggs' => [
                        'values' => $aggregation,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $filters
     * @return array<string, mixed>
     */
    protected function facetScopeQuery(string $query, array $filters): array
    {
        return [
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
                'filter' => array_values(array_filter($filters)),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    protected function facetBuckets(array $aggregations, string $name): array
    {
        /** @var array<int, array<string, mixed>> $buckets */
        $buckets = $aggregations[$name]['scope']['values']['buckets']
            ?? $aggregations[$name]['scope']['aggs']['values']['buckets']
            ?? $aggregations[$name]['aggs']['scope']['aggs']['values']['buckets']
            ?? $aggregations[$name]['aggs']['scope']['values']['buckets']
            ?? $aggregations[$name]['aggs']['values']['buckets']
            ?? $aggregations[$name]['values']['buckets']
            ?? $aggregations[$name]['buckets']
            ?? [];

        return $buckets;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    protected function visibilityFilters(): array
    {
        return [
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
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    protected function skillFilters(array $params): array
    {
        return $this->allSkillsFilters($params['skills'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    protected function salaryFilters(array $params): array
    {
        return array_values(array_filter([
            $this->minimumSalaryFilter($params['salary_min'] ?? null),
            $this->maximumSalaryFilter($params['salary_max'] ?? null),
        ]));
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $filters
     * @return array<string, mixed>
     */
    protected function booleanFilter(array $filters): array
    {
        $normalizedFilters = array_values(array_filter($filters));

        if ($normalizedFilters === []) {
            return ['match_all' => (object) []];
        }

        return [
            'bool' => [
                'filter' => $normalizedFilters,
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
     * @return array<string, mixed>|null
     */
    protected function anyTermsFilter(string $field, mixed $values): ?array
    {
        $normalizedValues = collect(Arr::wrap($values))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        if ($normalizedValues === []) {
            return null;
        }

        return [
            'bool' => [
                'should' => array_values(array_map(
                    fn (string $value): array => ['term' => [$field => $value]],
                    $normalizedValues,
                )),
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @return array<int, array<string, array<string, mixed>>>
     */
    protected function allSkillsFilters(mixed $values): array
    {
        return collect(Arr::wrap($values))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->map(fn (string $value): array => ['term' => ['skill_slugs' => $value]])
            ->values()
            ->all();
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
    protected function locationFilter(array $locations): ?array
    {
        if ($locations === []) {
            return null;
        }

        return [
            'bool' => [
                'should' => array_values(array_map(
                    fn (string $location): array => [
                        'term' => [
                            'location_slugs' => $location,
                        ],
                    ],
                    $locations,
                )),
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeLocations(mixed $value): array
    {
        return collect(Arr::wrap($value))
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter(fn (string $item): bool => $item !== '')
            ->map(fn (string $item): string => Str::slug($item))
            ->filter(fn (string $item): bool => $item !== '')
            ->unique()
            ->values()
            ->all();
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
