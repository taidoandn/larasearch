<?php

$mappingPath = config_path('job_listings_v1_mapping.json');

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
    'mapping_path' => $mappingPath,
    'mapping' => json_decode((string) file_get_contents($mappingPath), true, 512, JSON_THROW_ON_ERROR),
];
