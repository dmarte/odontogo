<?php

use App\Http\Controllers\DocumentToPdfController;
use App\Http\Controllers\UserInvitationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('activate/{team}/{token}', UserInvitationController::class)->name('invitation.join');

Route::get('pdf/budget/{budget}', [DocumentToPdfController::class, 'budget'])->name('print.budget')->middleware('auth');
Route::get('pdf/receipt/{receipt}', [DocumentToPdfController::class, 'receipt'])->name('print.receipt')->middleware('auth');
