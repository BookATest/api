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
    Route::apiResource('/appointments', 'AppointmentController');
    Route::put('/appointments/{appointment}/cancel', 'Appointment\\CancelController')->name('appointments.cancel');
    Route::delete('/appointments/{appointment}/schedule', 'Appointment\\ScheduleController@destroy')->name('appointments.schedule.destroy');

    // Audit Routes.
    Route::apiResource('/audits', 'AuditController')->only('index', 'show');

    // Booking Routes.
    // TODO

    // Clinic Routes.
    Route::apiResource('/clinics', 'ClinicController');

    // Eligible Answer Routes.
    // TODO

    // Question Routes.
    Route::apiResource('/questions', 'QuestionController')->only('index', 'store');

    // Report Routes.
    // TODO

    // Report Schedule Routes.
    Route::apiResource('/report-schedules', 'User\\ReportScheduleController');

    // Service User Routes.
    // TODO

    // Setting Routes.
    // TODO

    // Stat Routes.
    // TODO

    // User Routes.
    // TODO
});
