<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['apikey','idempotency'])->group(function () {
    Route::post('/emails', [EmailController::class, 'send']);
});

Route::get('/emails/{id}', [EmailController::class, 'show']);
Route::get('/emails', [EmailController::class, 'index']);

Route::post('/webhooks/{provider}', [WebhookController::class, 'handle']);
