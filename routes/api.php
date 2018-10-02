<?php

use Illuminate\Support\Facades\Route;

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

// API Routes.
Route::get('/', 'ApiController@v1');

// V1 Routes.
Route::prefix('v1')->namespace('V1')->group(function () {
    // Appointment Routes.
    Route::apiResource('appointments', 'AppointmentController');
    // TODO: Route::put('/appointments/{appointment}/cancel', 'Appointment\\CancelController')->name('appointments.cancel');
    // TODO: Route::delete('/appointments/{appointment}/schedule', 'Appointment\\ScheduleController@destroy')->name('appointments.schedule.destroy');

    // Audit Routes.
    // TODO

    // Booking Routes.
    // TODO

    // Clinic Routes.
    // TODO

    // Eligible Answer Routes.
    // TODO

    // Question Routes.
    // TODO

    // Report Routes.
    // TODO

    // Report Schedule Routes.
    // TODO

    // Service User Routes.
    // TODO

    // Setting Routes.
    // TODO

    // Stat Routes.
    // TODO

    // User Routes.
    // TODO
});
