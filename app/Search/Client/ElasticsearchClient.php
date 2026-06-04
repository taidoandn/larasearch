<?php

namespace App\Search\Client;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchClient
{
    public function build(): Client
    {
        $builder = ClientBuilder::create()
            ->setHosts((array) config('elasticsearch.hosts', []))
            ->setRetries((int) config('elasticsearch.retries', 0))
            ->setHttpClientOptions([
                'timeout' => (int) config('elasticsearch.timeout', 5),
            ]);

        $username = config('elasticsearch.username');
        $password = config('elasticsearch.password');
        $apiKey = config('elasticsearch.api_key');
        $caBundle = config('elasticsearch.ssl.ca_bundle');

        if (is_string($apiKey) && $apiKey !== '') {
            $builder->setApiKey($apiKey);
        } elseif (is_string($username) && $username !== '' && is_string($password) && $password !== '') {
            $builder->setBasicAuthentication($username, $password);
        }

        if (is_string($caBundle) && $caBundle !== '') {
            $builder->setCABundle($caBundle);
        }

        $builder->setSSLVerification((bool) config('elasticsearch.ssl.verify', true));

        return $builder->build();
    }
}
