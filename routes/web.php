<?php

use App\Http\Controllers\JobsController;
use App\Http\Controllers\JobShowController;
use App\Http\Controllers\JobSuggestController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'landing/index', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('jobs', JobsController::class)->name('jobs.index');
    Route::get('jobs/suggest', JobSuggestController::class)->name('jobs.suggest');
    Route::get('jobs/{job:slug}', JobShowController::class)
        ->where('job', '[A-Za-z0-9-]+')
        ->name('jobs.show');
});

require __DIR__.'/settings.php';
