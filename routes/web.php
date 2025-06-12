<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateExampleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Template engine example routes
Route::prefix('templates')->group(function () {
    Route::get('/', [TemplateExampleController::class, 'index']);
    Route::get('/blade', [TemplateExampleController::class, 'blade']);
    Route::get('/twig', [TemplateExampleController::class, 'twig']);
    Route::get('/plates', [TemplateExampleController::class, 'plates']);
    Route::get('/auto/{view}', [TemplateExampleController::class, 'autoDetect']);
});
