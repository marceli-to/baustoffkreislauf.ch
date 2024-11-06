<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\TranslationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/translations/{locale}', [TranslationController::class, 'fetch']);

Route::post('/event/register', [EventController::class, 'register']);
Route::get('/event/{eventId}', [EventController::class, 'get']);

Route::post('/course/register', [CourseController::class, 'register']);
Route::get('/course/{courseId}', [CourseController::class, 'get']);
