<?php

namespace App\Http\Controllers;

use App\Searchers\JobListingSearcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSuggestController extends Controller
{
    public function __invoke(Request $request, JobListingSearcher $searcher): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(
            $searcher->suggest((string) ($validated['q'] ?? '')),
        );
    }
}
