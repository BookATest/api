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

/*
 * V1 Routes.
 */
Route::prefix('v1')->namespace('V1')->group(function () {
    /*
     * Appointment Routes.
     */
    Route::apiResource('appointments', 'AppointmentController');
    Route::get('appointments.ics', 'Appointment\\IcsController')
        ->name('appointments.index.ics');
    Route::put('appointments/{appointment}/cancel', 'Appointment\\CancelController')
        ->name('appointments.cancel');
    Route::delete('appointments/{appointment}/schedule', 'Appointment\\ScheduleController@destroy')
        ->name('appointments.schedule.destroy');

    /*
     * Audit Routes.
     */
    Route::apiResource('audits', 'AuditController')
        ->only('index', 'show');

    /*
     * Booking Routes.
     */
    Route::post('bookings', 'BookingController@store')
        ->name('bookings.store');
    Route::post('bookings/eligibility', 'BookingController@eligibility')
        ->name('bookings.eligibility');

    /*
     * Clinic Routes.
     */
    Route::apiResource('clinics', 'ClinicController');

    /*
     * Eligible Answer Routes.
     */
    Route::get('clinics/{clinic}/eligible-answers', 'EligibleAnswerController@index')
        ->name('clinics.eligible-answers.index');
    Route::put('clinics/{clinic}/eligible-answers', 'EligibleAnswerController@update')
        ->name('clinics.eligible-answers.update');

    /*
     * Question Routes.
     */
    Route::apiResource('questions', 'QuestionController')
        ->only('index', 'store');

    /*
     * Report Routes.
     */
    Route::apiResource('reports', 'ReportController')
        ->only('index', 'store', 'show', 'destroy');
    Route::get('reports/{report}/download', 'Report\\DownloadController')
        ->name('reports.download');

    /*
     * Report Schedule Routes.
     */
    Route::apiResource('report-schedules', 'ReportScheduleController')
        ->only('index', 'store', 'show', 'destroy');

    /*
     * Service User Routes.
     */
    Route::post('service-users/access-code', 'ServiceUser\\AccessCodeController')
        ->name('service-users.access-code');
    Route::apiResource('service-users/token', 'ServiceUser\\TokenController')
        ->only('store', 'show');
    Route::apiResource('service-users', 'ServiceUserController')
        ->only('index', 'show');
    Route::apiResource('service-users.appointments', 'ServiceUser\\AppointmentController')
        ->only('index');

    /*
     * Setting Routes.
     */
    Route::get('settings', 'SettingController@index')
        ->name('settings.index');
    Route::put('settings', 'SettingController@update')
        ->name('settings.update');

    /*
     * Stat Routes.
     */
    Route::apiResource('stats', 'StatController')
        ->only('index');

    /*
     * User Routes.
     */
    Route::get('users/user', 'UserController@user')
        ->name('users.user');
    Route::apiResource('users', 'UserController');
    Route::put('users/{user}/calendar-feed-token', 'User\\CalendarFeedTokenController@update')
        ->name('users.calendar-feed-token.update');
    Route::get('users/{user}/profile-picture.jpg', 'User\\ProfilePictureController')
        ->name('users.profile-picture');
});
