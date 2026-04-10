<?php

namespace App\Http\Controllers;

use App\Contracts\SearchServiceInterface;
use App\Http\Requests\SearchRequest;
use App\Services\JobSearchFilters;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends Controller
{
    public function __invoke(SearchRequest $request, SearchServiceInterface $searchService): Response
    {
        $filters = JobSearchFilters::normalize($request->validated());

        return Inertia::render('jobs/index', [
            'results' => $searchService->search($filters),
            'filters' => $filters,
        ]);
    }
}
