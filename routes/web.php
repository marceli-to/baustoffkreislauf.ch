<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsletterController;

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

// Route::statamic('example', 'example-view', [
//    'title' => 'Example'
// ]);

Route::get('/newsletter/anmeldung-bestaetigen/{uuid}', [NewsletterController::class, 'confirm']);
Route::get('/newsletter/confirmation-inscription/{uuid}', [NewsletterController::class, 'confirm']);
Route::get('/newsletter/conferma-iscrizione/{uuid}', [NewsletterController::class, 'confirm']);