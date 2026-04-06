<?php

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
    'timeout' => (int) env('ELASTICSEARCH_TIMEOUT', 5),
    'indexes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX', 'job_listings_v1'),
    ],
    'aliases' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_ALIAS', 'job_listings_current'),
    ],
    'mapping' => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'filter' => [
                    'autocomplete_filter' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 2,
                        'max_gram' => 20,
                    ],
                ],
                'analyzer' => [
                    'autocomplete_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'autocomplete_filter'],
                    ],
                    'autocomplete_search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase'],
                    ],
                ],
            ],
        ],
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'keyword'],
                'slug' => ['type' => 'keyword'],
                'title' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => ['type' => 'keyword'],
                        'autocomplete' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete_analyzer',
                            'search_analyzer' => 'autocomplete_search_analyzer',
                        ],
                    ],
                ],
                'description' => ['type' => 'text'],
                'short_description' => ['type' => 'text'],
                'company_name' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => ['type' => 'keyword'],
                        'autocomplete' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete_analyzer',
                            'search_analyzer' => 'autocomplete_search_analyzer',
                        ],
                    ],
                ],
                'company_slug' => ['type' => 'keyword'],
                'locations' => ['type' => 'keyword'],
                'location_labels' => ['type' => 'keyword'],
                'category_names' => ['type' => 'keyword'],
                'skills' => ['type' => 'keyword'],
                'skills_text' => [
                    'type' => 'text',
                    'fields' => [
                        'autocomplete' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete_analyzer',
                            'search_analyzer' => 'autocomplete_search_analyzer',
                        ],
                    ],
                ],
                'job_type' => ['type' => 'keyword'],
                'work_model' => ['type' => 'keyword'],
                'experience_level' => ['type' => 'keyword'],
                'salary_min' => ['type' => 'integer'],
                'salary_max' => ['type' => 'integer'],
                'salary_currency' => ['type' => 'keyword'],
                'salary_is_visible' => ['type' => 'boolean'],
                'is_featured' => ['type' => 'boolean'],
                'is_active' => ['type' => 'boolean'],
                'published_at' => ['type' => 'date'],
                'expires_at' => ['type' => 'date'],
            ],
        ],
    ],
];
