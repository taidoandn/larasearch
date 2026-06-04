<?php

namespace App\Http\Controllers;

use App\Services\JobListingSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSuggestController extends Controller
{
    public function __invoke(Request $request, JobListingSearchService $searchService): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(
            $searchService->suggest((string) ($validated['q'] ?? '')),
        );
    }
}
