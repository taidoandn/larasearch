<?php

namespace App\Services;

class JobListingSearchIndex
{
    public const array DEFAULT_KEYWORD_FIELDS = [
        'title^3',
        'skills_text^2',
        'company_name^2',
        'description',
    ];

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
     * @param  array{fields?: array<int, string>, fuzziness?: string, type?: string}  $options
     * @return array<int, array<string, mixed>>
     */
    public function keywordMustClause(string $query, array $options = []): array
    {
        if (trim($query) === '') {
            return [['match_all' => (object) []]];
        }

        $multiMatch = [
            'query' => $query,
            'fields' => $options['fields'] ?? self::DEFAULT_KEYWORD_FIELDS,
        ];

        if (isset($options['fuzziness'])) {
            $multiMatch['fuzziness'] = $options['fuzziness'];
        }

        if (isset($options['type'])) {
            $multiMatch['type'] = $options['type'];
        }

        return [[
            'multi_match' => $multiMatch,
        ]];
    }
}
