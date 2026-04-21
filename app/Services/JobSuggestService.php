<?php

namespace App\Services;

class JobSuggestService
{
    protected const int MAX_SUGGESTIONS = 5;

    protected const int ELASTICSEARCH_HIT_FETCH_SIZE = 15;

    public function __construct(
        private readonly ElasticsearchClient $client,
    ) {}

    /**
     * @return array{items: array<int, array{label: string, type: string}>}
     */
    public function suggest(string $keyword): array
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return ['items' => []];
        }

        $response = $this->client->search((string) config('elasticsearch.aliases.job_listings'), [
            'size' => self::ELASTICSEARCH_HIT_FETCH_SIZE,
            '_source' => ['title', 'company_name', 'skills'],
            'query' => [
                'bool' => [
                    'must' => [[
                        'multi_match' => [
                            'query' => $keyword,
                            'type' => 'best_fields',
                            'fuzziness' => 'AUTO',
                            'fields' => [
                                'title.autocomplete^3',
                                'skills_text.autocomplete^2',
                                'company_name.autocomplete',
                            ],
                        ],
                    ]],
                    'filter' => [
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
                    ],
                ],
            ],
        ]);

        return [
            'items' => $this->collectSuggestions((array) data_get($response, 'hits.hits', []), $keyword),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $hits
     * @return array<int, array{label: string, type: string}>
     */
    protected function collectSuggestions(array $hits, string $keyword): array
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
    protected function mapSuggestionHit(array $hit, string $normalizedKeyword): array
    {
        $source = (array) ($hit['_source'] ?? []);
        $items = [];

        if (filled($source['title'] ?? null) && $this->matchesKeyword((string) $source['title'], $normalizedKeyword)) {
            $items[] = [
                'label' => (string) $source['title'],
                'type' => 'job_title',
            ];
        }

        foreach ((array) ($source['skills'] ?? []) as $skill) {
            if (is_string($skill) && $skill !== '' && $this->matchesKeyword($skill, $normalizedKeyword)) {
                $items[] = [
                    'label' => $skill,
                    'type' => 'skill',
                ];
            }
        }

        if (filled($source['company_name'] ?? null) && $this->matchesKeyword((string) $source['company_name'], $normalizedKeyword)) {
            $items[] = [
                'label' => (string) $source['company_name'],
                'type' => 'company',
            ];
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
}
