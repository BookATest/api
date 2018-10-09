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
 * API Routes.
 */
Route::get('/', 'ApiController@v1');

/*
 * V1 Routes.
 */
Route::prefix('v1')->namespace('V1')->group(function () {
    /*
     * Appointment Routes.
     */
    Route::apiResource('appointments', 'AppointmentController');
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
    // TODO

    /*
     * Clinic Routes.
     */
    Route::apiResource('clinics', 'ClinicController');

    /*
     * Eligible Answer Routes.
     */
    // TODO

    /*
     * Question Routes.
     */
    Route::apiResource('questions', 'QuestionController')
        ->only('index', 'store');

    /*
     * Report Routes.
     */
    // TODO

    /*
     * Report Schedule Routes.
     */
    // TODO

    /*
     * Service User Routes.
     */
    Route::post('service-users/access-code', 'ServiceUser\\AccessCodeController')
        ->name('service-users.access-code');
    Route::apiResource('service-users/token', 'ServiceUser\\TokenController')
        ->only('store', 'show');
    Route::apiResource('service-users', 'ServiceUserController')
        ->only('index', 'show');

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
    // TODO

    /*
     * User Routes.
     */
    Route::apiResource('users', 'UserController');
    Route::put('users/{user}/calendar-feed-token', 'User\\CalendarFeedTokenController@update')
        ->name('users.calendar-feed-token.update');
    Route::get('users/{user}/profile-picture.png', 'User\\ProfilePictureController')
        ->name('users.profile-picture');
});
