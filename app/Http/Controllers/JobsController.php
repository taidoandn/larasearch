<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Searchers\JobListingSearcher;
use App\Services\JobSearchFilters;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends Controller
{
    public function __invoke(SearchRequest $request, JobListingSearcher $searcher): Response
    {
        $filters = JobSearchFilters::normalize($request->validated());

        return Inertia::render('jobs/index', [
            'results' => $searcher->search($filters),
            'filters' => $filters,
        ]);
    }
}
