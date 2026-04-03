<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('search', 'search-results')->name('larasearch.search-results');
    Route::get('search/jobs/{job}', fn (string $job) => Inertia::render('job-detail', [
        'jobId' => $job,
    ]))->name('larasearch.job-detail');
});

require __DIR__.'/settings.php';
