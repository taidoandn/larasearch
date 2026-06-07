<?php

namespace App\Services;

use App\Search\Filters\JobListingFilters;
use App\Search\Searchers\JobListingSearcher;

class JobListingSearchService
{
    public function __construct(
        private readonly JobListingSearcher $searcher,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{filters: array<string, mixed>, results: array<string, mixed>}
     */
    public function search(array $input): array
    {
        $filters = JobListingFilters::normalize($input);

        return [
            'filters' => $filters,
            'results' => $this->searcher->search($filters),
        ];
    }

    /**
     * @return array{items: array<int, array{label: string, type: string}>}
     */
    public function suggest(string $keyword): array
    {
        return $this->searcher->suggest($keyword);
    }
}
