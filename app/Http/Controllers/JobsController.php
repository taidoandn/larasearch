<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\JobListingSearchService;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends Controller
{
    public function __invoke(SearchRequest $request, JobListingSearchService $searchService): Response
    {
        $search = $searchService->search($request->validated());

        return Inertia::render('jobs/index', [
            'results' => $search['results'],
            'filters' => $search['filters'],
        ]);
    }
}
