<?php

use App\Http\Controllers\H2hToolController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/run-tests');
Route::get('/systems', [H2hToolController::class, 'systemsPage']);
Route::get('/systems/{system}/edit', [H2hToolController::class, 'editSystemPage']);
Route::get('/auth-profiles', [H2hToolController::class, 'authProfilesPage']);
Route::get('/auth-profiles/{authProfile}/edit', [H2hToolController::class, 'editAuthProfilePage']);
Route::get('/endpoints', [H2hToolController::class, 'endpointsPage']);
Route::get('/endpoints/{endpoint}/edit', [H2hToolController::class, 'editEndpointPage']);
Route::get('/run-tests', [H2hToolController::class, 'runTestsPage']);

Route::post('/systems', [H2hToolController::class, 'storeSystem']);
Route::put('/systems/{system}', [H2hToolController::class, 'updateSystem']);
Route::post('/auth-profiles', [H2hToolController::class, 'storeAuthProfile']);
Route::put('/auth-profiles/{authProfile}', [H2hToolController::class, 'updateAuthProfile']);
Route::post('/endpoints', [H2hToolController::class, 'storeEndpoint']);
Route::put('/endpoints/{endpoint}', [H2hToolController::class, 'updateEndpoint']);
Route::post('/run-test', [H2hToolController::class, 'run']);
