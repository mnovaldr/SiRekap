<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicVisitController;

// Public presentation stats — stateless, rate-limited
Route::get('/public/stats', [PublicVisitController::class, 'getStats'])
    ->middleware('throttle:30,1')
    ->name('public.visit.stats.api');
