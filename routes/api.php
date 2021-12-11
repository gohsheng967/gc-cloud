<?php

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
Route::post('login', 'Api\Auth\ApiAuthController@login');
Route::post('report/apiSaveReport', 'Api\Report\ApiReportController@apiSaveReport');

Route::get('/helper/{code}', function ($code) {
    return App\Helpers\Helper::checkingCode($code);
});
Route::get('/helper', function () {
    return App\Helpers\Helper::getInfo();
});
Route::get('/write', function () {
    return App\Helpers\Helper::write();
});
