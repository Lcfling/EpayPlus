<?php

use Illuminate\Http\Request;

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
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//验证码
Route::get('/verify',                   'Admin\HomeController@verify');
//登陆模块
Route::group(['namespace'  => "Auth"], function () {
    Route::get('/login',                'LoginController@showLoginForm')->name('login');
    Route::post('/login',               'LoginController@login');
    Route::get('/logout',               'LoginController@logout')->name('logout');
});
//后台主要模块
Route::group(['namespace' => "Admin",'middleware' => ['auth', 'permission']], function () {
    Route::get('/',                     'HomeController@index');
    Route::get('/gewt',                 'HomeController@configr');
    Route::get('/index',                'HomeController@welcome');
    Route::post('/sort',                'HomeController@changeSort');
    Route::resource('/menus',           'MenuController');
    Route::resource('/logs',            'LogController');
    Route::resource('/users',           'UserController');
    Route::resource('/ucenter',         'UcenterController');
    Route::get('/userinfo',             'UserController@userInfo');
    Route::post('/saveinfo/{type}',     'UserController@saveInfo');
    Route::resource('/roles',           'RoleController');
    Route::resource('/permissions',     'PermissionController');
    Route::resource('/notices',         'NoticeController');
    Route::post('/noticesUpdate',       'NoticeController@update');
    Route::resource('/options',         'OptionController');
    Route::post('/optionsUpdate',       'OptionController@update');
    Route::resource('/codeuser',        'CodeUserController');
    Route::post('/codeuserUpdate',      'CodeUserController@update');
    Route::resource('/coderakemoney',   'CoderakemoneyController');
    Route::post('/coderakemoneyUpdate', 'CoderakemoneyController@update');
    Route::resource('/business',        'BusinessController');
    Route::post('/businessUpdate',      'BusinessController@update');
    Route::resource('/busdrawnone',     'BusdrawnoneController');
    Route::post('/busdrawnone/pass',    'BusdrawnoneController@pass');
    Route::post('/busdrawnone/reject',  'BusdrawnoneController@reject');
    Route::resource('/busdrawdone',     'BusdrawdoneController');
    Route::resource('/busdrawreject',   'BusdrawrejectController');
    Route::resource('/agent',           'AgentController');
    Route::post('/agentUpdate',         'AgentController@update');
});

Route::get('/phpinfo',function (Request $request){
   phpinfo();
});