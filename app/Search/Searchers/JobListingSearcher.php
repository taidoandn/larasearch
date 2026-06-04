<?php

namespace App\Search\Searchers;

use App\Enums\ExperienceLevel;
use App\Enums\JobType;
use App\Enums\WorkModel;
use App\Search\Builders\JobListingQueryBuilder;
use App\Search\Utils\SearchNormalizer;
use App\Services\JobSearchFilters;
use Elastic\Elasticsearch\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class JobListingSearcher
{
    private readonly JobListingQueryBuilder $queryBuilder;

    public function __construct(
        private readonly Client $client,
        ?JobListingQueryBuilder $queryBuilder = null,
    ) {
        $this->queryBuilder = $queryBuilder ?? new JobListingQueryBuilder;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function search(array $params): array
    {
        $filters = JobSearchFilters::normalize($params);

        $response = $this->client->search([
            'index' => $this->alias(),
            'body' => $this->queryBuilder->searchBody($filters),
        ])->asArray();

        return $this->formatResults($response, (int) $filters['page'], (int) $filters['per_page'], (string) $filters['sort']);
    }

    /**
     * @return array{items: array<int, array{label: string, type: string}>}
     */
    public function suggest(string $keyword): array
    {
        $keyword = SearchNormalizer::keyword($keyword);

        if ($keyword === '') {
            return ['items' => []];
        }

        $response = $this->client->search([
            'index' => $this->alias(),
            'body' => $this->queryBuilder->suggestBody($keyword),
        ])->asArray();

        return $this->formatSuggestions($response, $keyword);
    }

    public function alias(): string
    {
        return (string) config('elasticsearch.aliases.job_listings');
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    protected function formatResults(array $response, int $page, int $perPage, string $sort): array
    {
        $items = array_map(
            fn (array $hit): array => $this->formatHit($hit),
            $this->hits($response),
        );

        $paginator = new LengthAwarePaginator(
            items: $items,
            total: $this->totalHits($response),
            perPage: $perPage,
            currentPage: $page,
        );

        return [
            ...$paginator->toArray(),
            'facets' => $this->formatFacets((array) ($response['aggregations'] ?? [])),
            'sort' => $sort,
        ];
    }

    /**
     * @return array{items: array<int, array{label: string, type: string}>}
     */
    protected function formatSuggestions(array $response, string $keyword, int $limit = 5): array
    {
        $hits = $this->suggestionHits($response);

        if ($hits === []) {
            $hits = $this->hits($response);
        }

        return [
            'items' => $this->collectSuggestions($hits, $keyword, $limit),
        ];
    }

    protected function totalHits(array $response): int
    {
        return (int) data_get($response, 'hits.total.value', 0);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function hits(array $response): array
    {
        /** @var array<int, array<string, mixed>> $hits */
        $hits = data_get($response, 'hits.hits', []);

        return $hits;
    }

    /**
     * @param  array<string, mixed>  $hit
     * @return array<string, mixed>
     */
    protected function formatHit(array $hit): array
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
    protected function formatFacets(array $aggregations): array
    {
        return [
            'locations' => $this->facetItems($this->aggregationBuckets($aggregations, 'locations'), 'location_labels', 'location_slugs'),
            'categories' => $this->facetItems($this->aggregationBuckets($aggregations, 'categories'), 'category_names', 'category_slugs'),
            'skills' => $this->facetItems($this->aggregationBuckets($aggregations, 'skills'), 'skills', 'skill_slugs'),
            'job_types' => $this->facetItems($this->aggregationBuckets($aggregations, 'job_types')),
            'work_models' => $this->facetItems($this->aggregationBuckets($aggregations, 'work_models')),
            'experience_levels' => $this->facetItems($this->aggregationBuckets($aggregations, 'experience_levels')),
        ];
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @return array<int, array<string, mixed>>
     */
    protected function aggregationBuckets(array $aggregations, string $name): array
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
     * @param  array<int, array<string, mixed>>  $buckets
     * @return array<int, array<string, int|string>>
     */
    protected function facetItems(array $buckets, ?string $labelField = null, ?string $lookupField = null): array
    {
        return array_map(
            fn (array $bucket): array => [
                'value' => (string) ($bucket['key'] ?? ''),
                'label' => $this->firstFacetLabel($bucket, $labelField, $lookupField),
                'count' => (int) ($bucket['doc_count'] ?? 0),
            ],
            $buckets,
        );
    }

    protected function firstFacetLabel(array $bucket, ?string $labelField = null, ?string $lookupField = null): string
    {
        $source = (array) data_get($bucket, 'label.hits.hits.0._source', []);
        $bucketKey = (string) ($bucket['key'] ?? '');

        if ($labelField !== null) {
            $labels = data_get($source, $labelField);

            if (is_string($labels) && $labels !== '') {
                return $labels;
            }

            if (is_array($labels) && $labels !== [] && $lookupField !== null && is_array(data_get($source, $lookupField))) {
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
                $firstLabel = Arr::first($labels);

                if (is_string($firstLabel) && $firstLabel !== '') {
                    return $firstLabel;
                }
            }
        }

        foreach ($source as $value) {
            if (is_array($value) && $value !== []) {
                return (string) Arr::first($value);
            }

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $bucketKey;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function suggestionHits(array $response): array
    {
        return collect((array) data_get($response, 'suggest.job_listing_suggest.0.options', []))
            ->map(function (array $option): array {
                if (isset($option['_source']) && is_array($option['_source'])) {
                    return ['_source' => $option['_source']];
                }

                return ['_source' => ['title' => $option['text'] ?? '']];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $hits
     * @return array<int, array{label: string, type: string}>
     */
    protected function collectSuggestions(array $hits, string $keyword, int $limit): array
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

                if (count($items) === $limit) {
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
    protected function mapSuggestionHit(array $hit, string $normalizedKeyword): array
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

    protected function matchesKeyword(string $value, string $normalizedKeyword): bool
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

    protected function tokenMatchesKeyword(string $token, string $normalizedKeyword): bool
    {
        if (str_contains($token, $normalizedKeyword)) {
            return true;
        }

        if (abs(mb_strlen($token) - mb_strlen($normalizedKeyword)) > 2) {
            return false;
        }

        return levenshtein($token, $normalizedKeyword) <= $this->allowedDistance($normalizedKeyword);
    }

    protected function allowedDistance(string $normalizedKeyword): int
    {
        $length = mb_strlen($normalizedKeyword);

        return match (true) {
            $length <= 4 => 1,
            $length <= 8 => 2,
            default => 3,
        };
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
