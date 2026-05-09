<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\MemberController;
use App\Http\Controllers\OrganizationController;

Route::middleware(['auth', 'member.active'])->group(function () {

    Route::get('/members', [MemberController::class, 'index'])->middleware('member.permission:member.view');
    Route::get('/members/{member}', [MemberController::class, 'show']);

    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);

});


use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);



Route::get('/login', function () {
    return redirect('/auth/google/redirect');
})->name('login');

