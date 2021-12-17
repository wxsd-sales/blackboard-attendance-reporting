<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
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

Route::name('home')->get('/', [
    HomeController::class,
    'index'
]);
Route::name('dashboard')->get('/dashboard', [
    DashboardController::class,
    'index'
]);

// Authentication Routes
//Route::name('login')->get('/login', [
//    App\Http\Controllers\Auth\LoginController::class,
//    'showLoginForm'
//]);
//Route::post('/login', [
//    App\Http\Controllers\Auth\LoginController::class,
//    'login'
//]);

Route::name('logout')->post('/logout', [
    App\Http\Controllers\Auth\LoginController::class,
    'logout'
]);
Route::name('reset')->post('/reset', [
    App\Http\Controllers\Auth\LoginController::class,
    'logout'
]);

Route::name('login')->get('/login', [
    App\Http\Controllers\Auth\RegisterController::class,
    'showRegistrationForm'
]);
Route::name('setup')->get('/setup', [
    App\Http\Controllers\Auth\RegisterController::class,
    'showRegistrationForm'
]);
Route::post('/setup', [
    App\Http\Controllers\Auth\RegisterController::class,
    'register'
]);

Route::name('auth.email')->post('/auth/email/redirect', [
    App\Http\Controllers\Auth\RegisterController::class,
    'emailOauthRedirect'
]);

Route::name('auth.blackboard')->get('/auth/blackboard/redirect', [
    App\Http\Controllers\Auth\RegisterController::class,
    'blackboardOauthRedirect'
]);
Route::get('/auth/blackboard/callback', [
    App\Http\Controllers\Auth\RegisterController::class,
    'blackboardOauthCallback'
]);

Route::name('auth.webex')->get('/auth/webex/redirect', [
    App\Http\Controllers\Auth\RegisterController::class,
    'webexOauthRedirect'
]);
Route::get('/auth/webex/callback', [
    App\Http\Controllers\Auth\RegisterController::class,
    'webexOauthCallback'
]);

// Jobs
Route::get('refreshBlackboardTokens', [
    App\Http\Controllers\JobsController::class,
    'refreshBlackboardTokens'
]);
Route::get('refreshWebexTokens', [
    App\Http\Controllers\JobsController::class,
    'refreshWebexTokens'
]);
Route::get('retrieveBlackboardUserCourses', [
    App\Http\Controllers\JobsController::class,
    'retrieveBlackboardUserCourses'
]);
Route::get('retrieveBlackboardCourseUsers', [
    App\Http\Controllers\JobsController::class,
    'retrieveBlackboardCourseUsers'
]);
Route::get('retrieveWebexMeetings', [
    App\Http\Controllers\JobsController::class,
    'retrieveWebexMeetings'
]);
Route::get('retrieveWebexScheduledMeetings', [
    App\Http\Controllers\JobsController::class,
    'retrieveWebexScheduledMeetings'
]);
Route::get('retrieveWebexMeetingParticipants', [
    App\Http\Controllers\JobsController::class,
    'retrieveWebexMeetingParticipants'
]);
Route::get('performAttendanceSync', [
    App\Http\Controllers\JobsController::class,
    'performAttendanceSync'
]);

// Get/Post JSON
Route::name('blackboardUserCourses')->get('/blackboardUserCourses', [
    DashboardController::class,
    'getBlackboardUserCourses'
]);
Route::name('webexMeetings')->get('/webexMeetings', [
    DashboardController::class,
    'getWebexMeetings'
]);
Route::name('webexScheduledMeetings')->get('/webexScheduledMeetings', [
    DashboardController::class,
    'getWebexScheduledMeetings'
]);
