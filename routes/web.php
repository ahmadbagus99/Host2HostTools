<?php

use App\Http\Controllers\H2hToolController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/run-tests');
Route::get('/systems', [H2hToolController::class, 'systemsPage']);
Route::get('/auth-profiles', [H2hToolController::class, 'authProfilesPage']);
Route::get('/endpoints', [H2hToolController::class, 'endpointsPage']);
Route::get('/run-tests', [H2hToolController::class, 'runTestsPage']);

Route::post('/systems', [H2hToolController::class, 'storeSystem']);
Route::post('/auth-profiles', [H2hToolController::class, 'storeAuthProfile']);
Route::post('/endpoints', [H2hToolController::class, 'storeEndpoint']);
Route::post('/run-test', [H2hToolController::class, 'run']);
