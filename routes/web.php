<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DBController;
use App\Http\Controllers\KeycloakController;
use App\Http\Controllers\DashboardController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [DBController::class, 'home']);

Route::get('/login/keycloak', [KeycloakController::class, 'redirectToKeycloak']);
Route::get('/login/keycloak/callback', [KeycloakController::class, 'handleKeycloakCallback']);

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

    Route::get('/logout', [KeycloakController::class, 'logout']);
    Route::get('/logout', [KeycloakController::class, 'logoutCallBack']);
});
