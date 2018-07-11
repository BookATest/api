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

Route::prefix('v1')->group(function () {
    Route::get('/', 'ApiController@v1');

    Route::apiResource('/appointments', 'V1\\AppointmentController');
    Route::delete('/appointments/{appointment}/schedule', 'V1\\Appointment\\ScheduleController@destroy')->name('appointments.schedule.destroy');
});
