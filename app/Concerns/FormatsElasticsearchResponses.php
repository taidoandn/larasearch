<?php

namespace App\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

trait FormatsElasticsearchResponses
{
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
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function formatLengthAwareResults(array $items, int $total, int $page, int $perPage, array $extra = []): array
    {
        $paginator = new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
        );

        return [
            ...$paginator->toArray(),
            ...$extra,
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
