<?php

use Illuminate\Support\Facades\Route;

// API Routes are loaded separately via AppServiceProvider

// SPA Entry Point - All routes handled by Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
