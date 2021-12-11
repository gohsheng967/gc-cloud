<?php

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

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| administrator
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator']], function () {
    Route::GET('/users', 'Backend\Users\UsersController@index')->name('users');
    Route::GET('/users/add', 'Backend\Users\UsersController@add')->name('users.add');
    Route::POST('/users/create', 'Backend\Users\UsersController@create')->name('users.create');
    Route::GET('/users/edit/{id}', 'Backend\Users\UsersController@edit')->name('users.edit');
    Route::POST('/users/update', 'Backend\Users\UsersController@update')->name('users.update');
    Route::GET('/users/delete/{id}', 'Backend\Users\UsersController@delete')->name('users.delete');

    Route::GET('/settings', 'Backend\Setting\SettingsController@index')->name('settings');
    Route::POST('/settings/update', 'Backend\Setting\SettingsController@update')->name('settings.update');

    Route::GET('/users/import', 'Backend\Users\UsersController@import')->name('users.import');
    Route::POST('/users/importData', 'Backend\Users\UsersController@importData')->name('users.importData');
});

/*
|--------------------------------------------------------------------------
| administrator|admin|editor|guest
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|visitor|guest']], function () {
    Route::GET('/checkProductVerify', 'MainController@checkProductVerify')->name('checkProductVerify');

    Route::GET('/profile/details', 'Backend\Profile\ProfileController@details')->name('profile.details');
    Route::POST('/profile/update', 'Backend\Profile\ProfileController@update')->name('profile.update');
});


/*
|--------------------------------------------------------------------------
| administrator|admin|visitor
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|visitor']], function () {
    // Route::GET('/reports', 'Backend\Report\ReportController@index')->name('reports');
    Route::GET('/reports', 'Backend\ProductReport\ProductReportController@index')->name('reports');

});

/*
|--------------------------------------------------------------------------
| administrator|admin
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {
    Route::GET('/histories', 'Backend\History\HistoryController@index')->name('histories');
    Route::GET('/histories/add', 'Backend\History\HistoryController@add')->name('histories.add');
    Route::POST('/histories/create', 'Backend\History\HistoryController@create')->name('histories.create');
    Route::GET('/histories/edit/{id}', 'Backend\History\HistoryController@edit')->name('histories.edit');
    Route::POST('/histories/update', 'Backend\History\HistoryController@update')->name('histories.update');
    Route::GET('/histories/delete/{id}', 'Backend\History\HistoryController@delete')->name('histories.delete');

    Route::GET('/histories/import', 'Backend\History\HistoryController@import')->name('histories.import');
    Route::POST('/histories/importData', 'Backend\History\HistoryController@importData')->name('histories.importData');

    Route::GET('/analytic', 'Backend\Analytic\AnalyticController@index')->name('analytic');

    Route::GET('/product/add', 'Backend\Product\ProductController@add')->name('product.add');
    Route::POST('/product/create', 'Backend\Product\ProductController@create')->name('product.create');
    Route::GET('/product', 'Backend\Product\ProductController@index')->name('product');
    Route::GET('/product/edit/{id}', 'Backend\Product\ProductController@edit')->name('product.edit');
    Route::GET('/product/delete/{id}', 'Backend\Product\ProductController@delete')->name('product.delete');
    Route::POST('/product/update', 'Backend\Product\ProductController@update')->name('product.update');

    Route::GET('/product/import', 'Backend\Product\ProductController@import')->name('product.import');
    Route::POST('/product/importData', 'Backend\Product\ProductController@importData')->name('product.importData');

    Route::GET('/sku', 'Backend\Sku\SkuController@index')->name('sku');
    Route::GET('/sku/edit/{id}', 'Backend\Sku\SkuController@edit')->name('sku.edit');
    Route::GET('/sku/delete/{id}', 'Backend\Sku\SkuController@delete')->name('sku.delete');
    Route::POST('/sku/update', 'Backend\Sku\SkuController@update')->name('sku.update');
    Route::GET('/sku/add', 'Backend\Sku\SkuController@add')->name('sku.add');
    Route::POST('/sku/create', 'Backend\Sku\SkuController@create')->name('sku.create');

    Route::GET('/sku/import', 'Backend\Sku\SkuController@import')->name('sku.import');
    Route::POST('/sku/importData', 'Backend\Sku\SkuController@importData')->name('sku.importData');

    Route::GET('/batch/{sku_id}', 'Backend\Batch\BatchController@index')->name('batch');
    Route::GET('/batch/edit/{id}', 'Backend\Batch\BatchController@edit')->name('batch.edit');
    Route::GET('/batch/delete/{id}', 'Backend\Batch\BatchController@delete')->name('batch.delete');
    Route::POST('/batch/update', 'Backend\Batch\BatchController@update')->name('batch.update');
    Route::GET('/batch/add/{sku_id}', 'Backend\Batch\BatchController@add')->name('batch.add');
    Route::POST('/batch/create', 'Backend\Batch\BatchController@create')->name('batch.create');
    Route::GET('/batch/getBatch/{sku_id}', 'Backend\Batch\BatchController@getBatch')->name('batch.get');


});

Route::post('reinputkey/index/{code}', 'Utils\Activity\ReinputKeyController@index');
