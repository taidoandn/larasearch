<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;

class ElasticsearchClient
{
    public function __construct(
        private readonly Client $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        return $this->client->cluster()->health()->asArray();
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function createIndex(string $index, array $body): array
    {
        return $this->client->indices()->create([
            'index' => $index,
            'body' => $body,
        ])->asArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteIndex(string $index): array
    {
        return $this->client->indices()->delete([
            'index' => $index,
        ])->asArray();
    }

    /**
     * @param  array<int, array<string, mixed>>  $actions
     * @return array<string, mixed>
     */
    public function updateAliases(array $actions): array
    {
        return $this->client->indices()->updateAliases([
            'body' => [
                'actions' => $actions,
            ],
        ])->asArray();
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function search(string $index, array $body): array
    {
        return $this->client->search([
            'index' => $index,
            'body' => $body,
        ])->asArray();
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function indexDocument(string $index, string|int $id, array $document): array
    {
        return $this->client->index([
            'index' => $index,
            'id' => (string) $id,
            'body' => $document,
        ])->asArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteDocument(string $index, string|int $id): array
    {
        return $this->client->delete([
            'index' => $index,
            'id' => (string) $id,
        ])->asArray();
    }

    /**
     * @param  array<int, array<string, mixed>>  $operations
     * @return array<string, mixed>
     */
    public function bulk(array $operations): array
    {
        return $this->client->bulk([
            'body' => $operations,
        ])->asArray();
    }
}
