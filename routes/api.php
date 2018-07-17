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

    // Appointment Routes.
    Route::apiResource('/appointments', 'V1\\AppointmentController');
    Route::delete('/appointments/{appointment}/schedule', 'V1\\Appointment\\ScheduleController@destroy')->name('appointments.schedule.destroy');
    Route::put('/appointments/{appointment}/cancel', 'V1\\Appointment\\CancelController@update')->name('appointments.cancel.update');
    Route::apiResource('/users.appointments', 'V1\\User\\AppointmentController')->only('index');
    Route::apiResource('/clinics.appointments', 'V1\\Clinic\\AppointmentController')->only('index', 'store');
    Route::apiResource('/service-users.appointments', 'V1\\ServiceUser\\AppointmentController')->only('index', 'store');

    // Audit Routes.
    Route::apiResource('/audits', 'V1\\AuditController')->only('index');

    // Booking Routes.

    // Calendar Feed Routes.
    Route::put('/users/{user}/calendar-feed-token', 'V1\\User\\CalendarFeedTokenController@update')->name('users.calendar-feed-token.update');

    // Clinic Routes.
    Route::apiResource('/clinics', 'V1\\ClinicController');

    // Eligible Answer Routes.

    // Question Routes.
    Route::apiResource('/questions', 'V1\\QuestionController')->only('index', 'store');
});
