<?php

namespace App\Search\Builders;

class JobListingQueryBuilder extends BaseQueryBuilder
{
    private const int FACET_BUCKET_LIMIT = 1000;

    private const int SUGGESTION_LIMIT = 5;

    private const array DEFAULT_KEYWORD_FIELDS = [
        'title^3',
        'skills_text^2',
        'company_name^2',
        'description',
    ];

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function searchBody(array $filters): array
    {
        $query = (string) ($filters['q'] ?? '');
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($filters['per_page'] ?? 20)));
        $sort = (string) ($filters['sort'] ?? 'best_match');

        $visibilityFilters = $this->visibilityFilters();
        $salaryFilters = $this->salaryFilters($filters);
        $skillFilters = $this->allTermFilters('skill_slugs', (array) ($filters['skills'] ?? []));
        $locationFilter = $this->anyTermsFilter('location_slugs', (array) ($filters['location'] ?? []));
        $categoryFilter = $this->anyTermsFilter('category_slugs', (array) ($filters['category'] ?? []));
        $jobTypeFilter = $this->anyTermsFilter('job_type', (array) ($filters['job_type'] ?? []));
        $workModelFilter = $this->anyTermsFilter('work_model', (array) ($filters['work_model'] ?? []));
        $experienceLevelFilter = $this->anyTermsFilter('experience_level', (array) ($filters['experience_level'] ?? []));
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

        return $this
            ->paginate($page, $perPage)
            ->sortBy($this->sortClause($sort))
            ->withHighlight(['title', 'description'])
            ->withAggregations($query, $visibilityFilters, $salaryFilters, $skillFilters, $locationFilter, $categoryFilter, $jobTypeFilter, $workModelFilter, $experienceLevelFilter)
            ->withQuery($this->searchQuery($query, $sort, $resultFilters))
            ->build();
    }

    /**
     * @return array<string, mixed>
     */
    public function suggestBody(string $keyword): array
    {
        return [
            'size' => 0,
            '_source' => ['title', 'company_name', 'skills'],
            'query' => [
                'bool' => [
                    'filter' => $this->visibilityFilters(),
                ],
            ],
            'suggest' => [
                'job_listing_suggest' => [
                    'prefix' => $keyword,
                    'completion' => [
                        'field' => 'suggest',
                        'skip_duplicates' => true,
                        'size' => self::SUGGESTION_LIMIT,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function visibilityFilters(): array
    {
        return [
            $this->filterTerm('is_active', true),
            $this->filterRange('published_at', lte: 'now'),
            $this->boolQuery(
                should: [
                    $this->missingFilter('expires_at'),
                    $this->filterRange('expires_at', gt: 'now'),
                ],
                minimumShouldMatch: 1,
            ),
        ];
    }

    /**
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
     * @return array<string, mixed>
     */
    protected function searchQuery(string $query, string $sort, array $baseFilters): array
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
                'filter' => $this->filterTerm('is_featured', true),
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
     * @return array<string, mixed>
     */
    protected function minimumSalaryFilter(mixed $minimum): ?array
    {
        if (! is_numeric($minimum)) {
            return null;
        }

        $minimumValue = (int) $minimum;

        return $this->boolQuery(
            should: [
                $this->filterRange('salary_max', gte: $minimumValue),
                $this->boolQuery(must: [
                    $this->missingFilter('salary_max'),
                    $this->filterRange('salary_min', gte: $minimumValue),
                ]),
            ],
            minimumShouldMatch: 1,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function maximumSalaryFilter(mixed $maximum): ?array
    {
        if (! is_numeric($maximum)) {
            return null;
        }

        $maximumValue = (int) $maximum;

        return $this->boolQuery(
            should: [
                $this->filterRange('salary_min', lte: $maximumValue),
                $this->boolQuery(must: [
                    $this->missingFilter('salary_min'),
                    $this->filterRange('salary_max', lte: $maximumValue),
                ]),
            ],
            minimumShouldMatch: 1,
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

    protected function withQuery(array $query): static
    {
        $this->body['query'] = $query;

        return $this;
    }

    protected function withAggregations(
        string $query,
        array $visibilityFilters,
        array $salaryFilters,
        array $skillFilters,
        ?array $locationFilter,
        ?array $categoryFilter,
        ?array $jobTypeFilter,
        ?array $workModelFilter,
        ?array $experienceLevelFilter,
    ): static {
        $this->body['aggs'] = [
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
        ];

        return $this;
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $filters
     * @param  array<string, mixed>  $aggregation
     * @return array<string, mixed>
     */
    protected function scopedFacetAggregation(string $query, array $filters, array $aggregation): array
    {
        return $this->globalScopedAggregation(
            $this->boolQuery(
                must: $this->keywordMustClause($query, self::DEFAULT_KEYWORD_FIELDS),
                filter: array_values(array_filter($filters)),
            ),
            $aggregation,
        );
    }
}
