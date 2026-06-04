<?php

namespace App\Search\Indexers;

use Elastic\Elasticsearch\Client;

abstract class BaseIndexer
{
    public function __construct(
        protected readonly Client $client,
    ) {}

    abstract public function alias(): string;

    abstract public function configuredIndex(): string;

    abstract public function indexPrefix(): string;

    /**
     * @return array<string, mixed>
     */
    abstract public function mapping(): array;

    public function createIndex(?string $index = null): string
    {
        $indexName = $index ?: $this->configuredIndex();

        $this->client->indices()->create([
            'index' => $indexName,
            'body' => $this->mapping(),
        ])->asArray();

        return $indexName;
    }

    public function deleteIndex(?string $index = null): string
    {
        $indexName = $index ?: $this->configuredIndex();

        $this->client->indices()->delete([
            'index' => $indexName,
        ])->asArray();

        return $indexName;
    }

    public function refresh(string $index): void
    {
        $this->client->indices()->refresh([
            'index' => $index,
        ])->asArray();
    }

    public function switchAlias(string $index, ?string $alias = null): string
    {
        $aliasName = $alias ?: $this->alias();

        $this->client->indices()->updateAliases([
            'body' => [
                'actions' => [
                    [
                        'remove' => [
                            'index' => '*',
                            'alias' => $aliasName,
                        ],
                    ],
                    [
                        'add' => [
                            'index' => $index,
                            'alias' => $aliasName,
                        ],
                    ],
                ],
            ],
        ])->asArray();

        return $aliasName;
    }

    /**
     * @return array<int, string>
     */
    public function cleanupOldIndices(int $keep = 2): array
    {
        $indices = $this->client->indices()->get([
            'index' => $this->indexPrefix().'*',
        ])->asArray();

        $activeIndices = $this->activeAliasIndices($indices);
        $deleteCandidates = collect(array_keys($indices))
            ->reject(fn (string $index): bool => in_array($index, $activeIndices, true))
            ->sort()
            ->values();

        $toDelete = $deleteCandidates
            ->take(max(0, $deleteCandidates->count() - $keep))
            ->values()
            ->all();

        foreach ($toDelete as $index) {
            $this->deleteIndex($index);
        }

        return $toDelete;
    }

    /**
     * @param  array<string, mixed>  $indices
     * @return array<int, string>
     */
    protected function activeAliasIndices(array $indices): array
    {
        return collect($indices)
            ->filter(fn (array $definition): bool => array_key_exists($this->alias(), (array) ($definition['aliases'] ?? [])))
            ->keys()
            ->values()
            ->all();
    }
}
