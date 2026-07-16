<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicVisitController;

// Public presentation stats — stateless, no auth required
Route::get('/public/stats', [PublicVisitController::class, 'getStats'])->name('public.visit.stats.api');
