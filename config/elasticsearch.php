<?php

$hosts = array_values(array_filter(array_map(
    static fn (string $host): string => trim($host),
    explode(',', (string) env('ELASTICSEARCH_HOSTS', env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'))),
)));
$indices = require __DIR__.'/elasticsearch_indices.php';

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'host' => $hosts[0] ?? 'http://elasticsearch:9200',
    'hosts' => $hosts === [] ? ['http://elasticsearch:9200'] : $hosts,
    'username' => env('ELASTICSEARCH_USERNAME'),
    'password' => env('ELASTICSEARCH_PASSWORD'),
    'api_key' => env('ELASTICSEARCH_API_KEY'),
    'timeout' => (int) env('ELASTICSEARCH_TIMEOUT', 5),
    'retries' => (int) env('ELASTICSEARCH_RETRIES', 2),
    'ssl' => [
        'verify' => (bool) env('ELASTICSEARCH_SSL_VERIFY', true),
        'ca_bundle' => env('ELASTICSEARCH_CA_BUNDLE'),
    ],
    'queue' => env('ELASTICSEARCH_QUEUE', 'default'),
    'cleanup' => [
        'keep' => (int) env('ELASTICSEARCH_CLEANUP_KEEP', 2),
    ],
    'indexes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX', 'job_listings_v1'),
    ],
    'index_prefixes' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_INDEX_PREFIX', 'job_listings_'),
    ],
    'aliases' => [
        'job_listings' => env('ELASTICSEARCH_JOB_LISTINGS_ALIAS', 'job_listings_current'),
    ],
    'indices' => $indices,
    'mapping_path' => config_path('elasticsearch_indices.php'),
    'mapping' => $indices['job_listings'],
];
