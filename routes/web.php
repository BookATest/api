<?php

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

// Home route.
Route::get('/', 'HomeController')->name('home');

Route::namespace('Auth')->group(function () {
// Authentication Routes.
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::get('login/code', 'LoginController@showOtpForm')->name('login.code');
    Route::post('login/code', 'LoginController@otp');
    Route::post('logout', 'LoginController@logout')->name('logout');

    // Password Reset Routes.
    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');
});

// API Docs Routes.
Route::resource('docs', 'DocsController')
    ->only('index');
Route::get('docs/openapi.json', 'DocsController@openapi')
    ->name('docs.openapi');

// Temporary DNA Routes - TODO: Remove these and replace with endpoints on the admin app.
Route::get('appointments/{appointment}/did-not-attend/{payload}', function (\App\Models\Appointment $appointment, string $payload) {
    try {
        $didNotAttend = json_decode(decrypt($payload), true);
    } catch (\Exception $exception) {
        return response('The payload is invalid');
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $didNotAttend): \App\Models\Appointment {
        return $appointment->setDnaStatus($didNotAttend);
    });

    return response('Did not attend status updated!');
})->name('appointments.did-not-attend');
