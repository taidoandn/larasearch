<?php

namespace App\Services;

use App\Concerns\BuildsElasticsearchQueries;
use App\Concerns\FormatsElasticsearchResponses;
use App\Contracts\SearchServiceInterface;
use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Models\JobListing;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class JobListingSearchService implements SearchServiceInterface
{
    use BuildsElasticsearchQueries;
    use FormatsElasticsearchResponses;

    private const int FACET_BUCKET_LIMIT = 1000;

    private const int MAX_SUGGESTIONS = 5;

    private const int SUGGESTION_HIT_FETCH_SIZE = 15;

    private const array DEFAULT_KEYWORD_FIELDS = [
        'title^3',
        'skills_text^2',
        'company_name^2',
        'description',
    ];

    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function search(array $params): array
    {
        $query = trim((string) ($params['q'] ?? ''));
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($params['per_page'] ?? 20)));
        $sort = (string) ($params['sort'] ?? 'best_match');
        $locations = $this->normalizeLocations($params['location'] ?? null);
        $visibilityFilters = $this->visibilityFilters();
        $salaryFilters = $this->salaryFilters($params);
        $skillFilters = $this->allTermFilters('skill_slugs', $this->normalizedStringValues($params['skills'] ?? null));
        $locationFilter = $this->locationFilter($locations);
        $categoryFilter = $this->anyTermsFilter('category_slugs', $this->normalizedStringValues($params['category'] ?? null));
        $jobTypeFilter = $this->anyTermsFilter('job_type', $this->normalizedStringValues($params['job_type'] ?? null));
        $workModelFilter = $this->anyTermsFilter('work_model', $this->normalizedStringValues($params['work_model'] ?? null));
        $experienceLevelFilter = $this->anyTermsFilter('experience_level', $this->normalizedStringValues($params['experience_level'] ?? null));
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

        $response = $this->client->search([
            'index' => $this->alias(),
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'sort' => $this->sortClause($sort),
                'query' => $this->searchQuery($query, $sort, $resultFilters),
                'aggs' => [
                    'locations' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $categoryFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                        $this->labeledTermsAggregation('location_slugs', 'location_labels', self::FACET_BUCKET_LIMIT),
                    ),
                    'categories' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                        $this->labeledTermsAggregation('category_slugs', 'category_names', self::FACET_BUCKET_LIMIT),
                    ),
                    'skills' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter],
                        $this->labeledTermsAggregation('skill_slugs', 'skills', self::FACET_BUCKET_LIMIT),
                    ),
                    'job_types' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $workModelFilter, $experienceLevelFilter],
                        $this->termsAggregation('job_type', self::FACET_BUCKET_LIMIT),
                    ),
                    'work_models' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $experienceLevelFilter],
                        $this->termsAggregation('work_model', self::FACET_BUCKET_LIMIT),
                    ),
                    'experience_levels' => $this->scopedFacetAggregation(
                        $query,
                        [...$visibilityFilters, ...$salaryFilters, ...$skillFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $workModelFilter],
                        $this->termsAggregation('experience_level', self::FACET_BUCKET_LIMIT),
                    ),
                ],
                'highlight' => [
                    'fields' => [
                        'title' => (object) [],
                        'description' => (object) [],
                    ],
                ],
            ],
        ])->asArray();

        return $this->formatSearchResults($response, $page, $perPage, $sort);
    }

    /**
     * @return array{items: array<int, array{label: string, type: string}>}
     */
    public function suggest(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return ['items' => []];
        }

        $response = $this->client->search([
            'index' => $this->alias(),
            'body' => [
                'size' => self::SUGGESTION_HIT_FETCH_SIZE,
                '_source' => ['title', 'company_name', 'skills'],
                'query' => [
                    'bool' => [
                        'must' => $this->keywordMustClause($keyword, [
                            'title.autocomplete^3',
                            'skills_text.autocomplete^2',
                            'company_name.autocomplete',
                        ], [
                            'fuzziness' => 'AUTO',
                            'type' => 'best_fields',
                        ]),
                        'filter' => $this->visibilityFilters(),
                    ],
                ],
            ],
        ])->asArray();

        return [
            'items' => $this->collectSuggestions($this->hits($response), $keyword),
        ];
    }

    public function indexJobListing(JobListing $jobListing): void
    {
        $this->client->index([
            'index' => $this->alias(),
            'id' => (string) $jobListing->getKey(),
            'body' => $jobListing->toSearchDocument(),
        ])->asArray();
    }

    public function deleteJobListing(int $jobListingId): void
    {
        $this->client->delete([
            'index' => $this->alias(),
            'id' => (string) $jobListingId,
        ])->asArray();
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function visibilityFilters(): array
    {
        return [
            $this->termFilter('is_active', true),
            $this->rangeFilter('published_at', lte: 'now'),
            $this->boolQuery(
                should: [
                    $this->missingFilter('expires_at'),
                    $this->rangeFilter('expires_at', gt: 'now'),
                ],
                minimumShouldMatch: 1,
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $baseFilters
     * @return array<string, mixed>
     */
    private function searchQuery(string $query, string $sort, array $baseFilters): array
    {
        $baseQuery = $this->boolQuery(
            must: $this->keywordMustClause($query, self::DEFAULT_KEYWORD_FIELDS),
            filter: $baseFilters,
        );

        if ($sort !== 'best_match') {
            return $baseQuery;
        }

        return $this->functionScoreQuery($baseQuery, [
            [
                'filter' => $this->termFilter('is_featured', true),
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
        ]);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function formatSearchResults(array $response, int $page, int $perPage, string $sort): array
    {
        $items = array_map(
            fn (array $hit): array => $this->formatSearchHit($hit),
            $this->hits($response),
        );

        return $this->formatLengthAwareResults(
            items: $items,
            total: $this->totalHits($response),
            page: $page,
            perPage: $perPage,
            extra: [
                'facets' => $this->formatFacets((array) ($response['aggregations'] ?? [])),
                'sort' => $sort,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $hit
     * @return array<string, mixed>
     */
    private function formatSearchHit(array $hit): array
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
    private function formatFacets(array $aggregations): array
    {
        return [
            'locations' => $this->facetItems(
                $this->aggregationBuckets($aggregations, 'locations'),
                'location_labels',
                'location_slugs',
            ),
            'categories' => $this->facetItems(
                $this->aggregationBuckets($aggregations, 'categories'),
                'category_names',
                'category_slugs',
            ),
            'skills' => $this->facetItems(
                $this->aggregationBuckets($aggregations, 'skills'),
                'skills',
                'skill_slugs',
            ),
            'job_types' => $this->facetItems($this->aggregationBuckets($aggregations, 'job_types')),
            'work_models' => $this->facetItems($this->aggregationBuckets($aggregations, 'work_models')),
            'experience_levels' => $this->facetItems($this->aggregationBuckets($aggregations, 'experience_levels')),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $filters
     * @param  array<string, mixed>  $aggregation
     * @return array<string, mixed>
     */
    private function scopedFacetAggregation(string $query, array $filters, array $aggregation): array
    {
        return $this->globalScopedAggregation(
            $this->boolQuery(
                must: $this->keywordMustClause($query, self::DEFAULT_KEYWORD_FIELDS),
                filter: array_values(array_filter($filters)),
            ),
            $aggregation,
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    private function salaryFilters(array $params): array
    {
        return array_values(array_filter([
            $this->minimumSalaryFilter($params['salary_min'] ?? null),
            $this->maximumSalaryFilter($params['salary_max'] ?? null),
        ]));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sortClause(string $sort): array
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
    private function minimumSalaryFilter(mixed $minimum): ?array
    {
        if (! is_numeric($minimum)) {
            return null;
        }

        $minimumValue = (int) $minimum;

        return $this->boolQuery(
            should: [
                $this->rangeFilter('salary_max', gte: $minimumValue),
                $this->boolQuery(must: [
                    $this->missingFilter('salary_max'),
                    $this->rangeFilter('salary_min', gte: $minimumValue),
                ]),
            ],
            minimumShouldMatch: 1,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function maximumSalaryFilter(mixed $maximum): ?array
    {
        if (! is_numeric($maximum)) {
            return null;
        }

        $maximumValue = (int) $maximum;

        return $this->boolQuery(
            should: [
                $this->rangeFilter('salary_min', lte: $maximumValue),
                $this->boolQuery(must: [
                    $this->missingFilter('salary_min'),
                    $this->rangeFilter('salary_max', lte: $maximumValue),
                ]),
            ],
            minimumShouldMatch: 1,
        );
    }

    /**
     * @param  array<int, string>  $locations
     * @return array<string, mixed>|null
     */
    private function locationFilter(array $locations): ?array
    {
        return $this->anyTermsFilter('location_slugs', $locations);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeLocations(mixed $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $item): string => Str::slug($item),
            $this->normalizedStringValues($value),
        ))));
    }

    /**
     * @return array<int, string>
     */
    private function normalizedStringValues(mixed $value): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $item): string => trim((string) $item),
            Arr::wrap($value),
        ), fn (string $item): bool => $item !== ''));
    }

    /**
     * @param  array<int, array<string, mixed>>  $hits
     * @return array<int, array{label: string, type: string}>
     */
    private function collectSuggestions(array $hits, string $keyword): array
    {
        $normalizedKeyword = mb_strtolower($keyword);
        $items = [];
        $seen = [];

        foreach ($hits as $hit) {
            foreach ($this->mapSuggestionHit($hit, $normalizedKeyword) as $item) {
                $key = "{$item['type']}:{$item['label']}";

                if (array_key_exists($key, $seen)) {
                    continue;
                }

                $seen[$key] = true;
                $items[] = $item;

                if (count($items) === self::MAX_SUGGESTIONS) {
                    return $items;
                }
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $hit
     * @return array<int, array{label: string, type: string}>
     */
    private function mapSuggestionHit(array $hit, string $normalizedKeyword): array
    {
        $source = (array) ($hit['_source'] ?? []);
        $items = [];

        if (filled($source['title'] ?? null) && $this->matchesKeyword((string) $source['title'], $normalizedKeyword)) {
            $items[] = ['label' => (string) $source['title'], 'type' => 'job_title'];
        }

        foreach ((array) ($source['skills'] ?? []) as $skill) {
            if (is_string($skill) && $skill !== '' && $this->matchesKeyword($skill, $normalizedKeyword)) {
                $items[] = ['label' => $skill, 'type' => 'skill'];
            }
        }

        if (filled($source['company_name'] ?? null) && $this->matchesKeyword((string) $source['company_name'], $normalizedKeyword)) {
            $items[] = ['label' => (string) $source['company_name'], 'type' => 'company'];
        }

        return $items;
    }

    private function matchesKeyword(string $value, string $normalizedKeyword): bool
    {
        $normalizedValue = mb_strtolower($value);

        if (str_contains($normalizedValue, $normalizedKeyword)) {
            return true;
        }

        foreach (preg_split('/[^[:alnum:]]+/u', $normalizedValue) ?: [] as $token) {
            if ($token !== '' && $this->tokenMatchesKeyword($token, $normalizedKeyword)) {
                return true;
            }
        }

        return false;
    }

    private function tokenMatchesKeyword(string $token, string $normalizedKeyword): bool
    {
        if (str_contains($token, $normalizedKeyword)) {
            return true;
        }

        if (abs(mb_strlen($token) - mb_strlen($normalizedKeyword)) > 2) {
            return false;
        }

        return levenshtein($token, $normalizedKeyword) <= $this->allowedDistance($normalizedKeyword);
    }

    private function allowedDistance(string $normalizedKeyword): int
    {
        $length = mb_strlen($normalizedKeyword);

        return match (true) {
            $length <= 4 => 1,
            $length <= 8 => 2,
            default => 3,
        };
    }
}
