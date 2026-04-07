<?php

use App\Http\Controllers\JobsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'landing/index', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('jobs', JobsController::class)->name('jobs.index');
    Route::get('jobs/{job}', fn (string $job) => Inertia::render('jobs/show', [
        'jobId' => $job,
    ]))->name('jobs.show');

    Route::get('search', fn (Request $request) => redirect()->route('jobs.index', $request->query(), 301));
    Route::get('search/jobs/{job}', fn (string $job) => redirect()->route('jobs.show', [
        'job' => $job,
    ], 301));
});

require __DIR__.'/settings.php';
