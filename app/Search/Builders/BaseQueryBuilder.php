<?php

namespace App\Search\Builders;

class BaseQueryBuilder
{
    /** @var array<string, mixed> */
    protected array $body = [];

    public function paginate(int $page, int $perPage): static
    {
        $this->body['from'] = ($page - 1) * $perPage;
        $this->body['size'] = $perPage;

        return $this;
    }

    public function filterTerm(string $field, bool|int|float|string $value): array
    {
        return ['term' => [$field => $value]];
    }

    public function filterRange(
        string $field,
        int|float|string|null $gt = null,
        int|float|string|null $gte = null,
        int|float|string|null $lt = null,
        int|float|string|null $lte = null,
    ): ?array {
        $range = array_filter([
            'gt' => $gt,
            'gte' => $gte,
            'lt' => $lt,
            'lte' => $lte,
        ], fn (int|float|string|null $value): bool => $value !== null);

        if ($range === []) {
            return null;
        }

        return ['range' => [$field => $range]];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sort
     */
    public function sortBy(array $sort): static
    {
        $this->body['sort'] = $sort;

        return $this;
    }

    /**
     * @param  array<int, string>  $fields
     */
    public function withHighlight(array $fields): static
    {
        $this->body['highlight'] = [
            'fields' => collect($fields)
                ->mapWithKeys(fn (string $field): array => [$field => (object) []])
                ->all(),
        ];

        return $this;
    }

    public function withTermsAggregation(string $name, string $field, int $size = 1000): static
    {
        $this->body['aggs'][$name] = $this->termsAggregation($field, $size);

        return $this;
    }

    public function withSuggestion(string $name, string $field, string $prefix, int $size = 5): static
    {
        $this->body['suggest'][$name] = [
            'prefix' => $prefix,
            'completion' => [
                'field' => $field,
                'skip_duplicates' => true,
                'size' => $size,
            ],
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    protected function matchAllQuery(): array
    {
        return ['match_all' => (object) []];
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function multiMatchQuery(string $query, array $fields, array $options = []): array
    {
        return [
            'multi_match' => [
                'query' => $query,
                'fields' => $fields,
                ...$options,
            ],
        ];
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    protected function keywordMustClause(string $query, array $fields = [], array $options = []): array
    {
        if (trim($query) === '') {
            return [$this->matchAllQuery()];
        }

        return [$this->multiMatchQuery($query, $fields, $options)];
    }

    /**
     * @param  array<int, bool|int|float|string>  $values
     * @return array<string, mixed>|null
     */
    protected function anyTermsFilter(string $field, array $values): ?array
    {
        $normalizedValues = $this->filledScalarValues($values);

        if ($normalizedValues === []) {
            return null;
        }

        return [
            'bool' => [
                'should' => array_map(
                    fn (bool|int|float|string $value): array => $this->filterTerm($field, $value),
                    $normalizedValues,
                ),
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * @param  array<int, bool|int|float|string>  $values
     * @return array<int, array<string, array<string, bool|int|float|string>>>
     */
    protected function allTermFilters(string $field, array $values): array
    {
        return array_map(
            fn (bool|int|float|string $value): array => $this->filterTerm($field, $value),
            $this->filledScalarValues($values),
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function existsFilter(string $field): array
    {
        return ['exists' => ['field' => $field]];
    }

    /**
     * @return array<string, mixed>
     */
    protected function missingFilter(string $field): array
    {
        return ['bool' => ['must_not' => [$this->existsFilter($field)]]];
    }

    /**
     * @param  array<int, array<string, mixed>|null>  $must
     * @param  array<int, array<string, mixed>|null>  $filter
     * @param  array<int, array<string, mixed>|null>  $should
     * @param  array<int, array<string, mixed>|null>  $mustNot
     * @return array<string, mixed>
     */
    protected function boolQuery(
        array $must = [],
        array $filter = [],
        array $should = [],
        array $mustNot = [],
        ?int $minimumShouldMatch = null,
    ): array {
        $bool = array_filter([
            'must' => array_values(array_filter($must)),
            'filter' => array_values(array_filter($filter)),
            'should' => array_values(array_filter($should)),
            'must_not' => array_values(array_filter($mustNot)),
            'minimum_should_match' => $minimumShouldMatch,
        ], fn (mixed $value): bool => $value !== [] && $value !== null);

        return ['bool' => $bool];
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<int, array<string, mixed>>  $functions
     * @return array<string, mixed>
     */
    protected function functionScoreQuery(array $query, array $functions): array
    {
        return [
            'function_score' => [
                'query' => $query,
                'functions' => $functions,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function termsAggregation(string $field, int $size = 1000): array
    {
        return [
            'terms' => [
                'field' => $field,
                'size' => $size,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function labeledTermsAggregation(string $field, string $labelField, int $size = 1000): array
    {
        return [
            ...$this->termsAggregation($field, $size),
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
     * @param  array<string, mixed>  $scopeFilter
     * @param  array<string, mixed>  $aggregation
     * @return array<string, mixed>
     */
    protected function globalScopedAggregation(array $scopeFilter, array $aggregation): array
    {
        return [
            'global' => (object) [],
            'aggs' => [
                'scope' => [
                    'filter' => $scopeFilter,
                    'aggs' => [
                        'values' => $aggregation,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, bool|int|float|string>  $values
     * @return array<int, bool|int|float|string>
     */
    protected function filledScalarValues(array $values): array
    {
        return array_values(array_filter(
            array_map(
                fn (bool|int|float|string $value): bool|int|float|string => is_string($value) ? trim($value) : $value,
                $values,
            ),
            fn (bool|int|float|string $value): bool => ! is_string($value) || $value !== '',
        ));
    }
}
