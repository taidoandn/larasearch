<?php

namespace App\Http\Controllers;

use App\Contracts\SearchServiceInterface;
use App\Http\Requests\SearchRequest;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends Controller
{
    public function __invoke(SearchRequest $request, SearchServiceInterface $searchService): Response
    {
        $filters = $this->normalizedFilters($request->validated());

        return Inertia::render('jobs/index', [
            'results' => $searchService->search($filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function normalizedFilters(array $validated): array
    {
        return [
            'q' => $validated['q'] ?? '',
            'location' => $validated['location'] ?? '',
            'category' => $validated['category'] ?? '',
            'skills' => $validated['skills'] ?? [],
            'job_type' => $validated['job_type'] ?? '',
            'work_model' => $validated['work_model'] ?? '',
            'experience_level' => $validated['experience_level'] ?? '',
            'salary_min' => isset($validated['salary_min']) ? (int) $validated['salary_min'] : null,
            'salary_max' => isset($validated['salary_max']) ? (int) $validated['salary_max'] : null,
            'sort' => $validated['sort'] ?? 'best_match',
            'page' => isset($validated['page']) ? (int) $validated['page'] : 1,
            'per_page' => isset($validated['per_page']) ? (int) $validated['per_page'] : 20,
        ];
    }
}
