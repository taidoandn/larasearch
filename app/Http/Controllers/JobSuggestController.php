<?php

namespace App\Http\Controllers;

use App\Services\JobSuggestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSuggestController extends Controller
{
    public function __invoke(Request $request, JobSuggestService $suggestService): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(
            $suggestService->suggest((string) ($validated['q'] ?? '')),
        );
    }
}
