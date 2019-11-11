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
    Route::resource('/options',         'OptionController');
    Route::post('/optionsUpdate',       'OptionController@update');
    Route::resource('/notices',         'NoticeController');
    Route::post('/noticesUpdate',       'NoticeController@update');
    Route::resource('/callcenter',      'CallcenterController');
    Route::post('/callcenterUpdate',    'CallcenterController@update');
    Route::resource('/business',        'BusinessController');
    Route::get('/business/bankinfo/{id}', 'BusinessController@bankinfo');
    Route::get('/business/buspwd/{id}', 'BusinessController@buspwd');
    Route::post('/busnewpwd',           'BusinessController@busnewpwd');
    Route::get('/business/buspayword/{id}','BusinessController@buspayword');
    Route::post('/busnewpayword',       'BusinessController@busnewpayword');
    Route::get('/business/busfee/{id}', 'BusinessController@busfee');
    Route::post('/busnewfee',           'BusinessController@busnewfee');
    Route::post('/businessUpdate',      'BusinessController@update');
    Route::resource('/busdrawnone',     'BusdrawnoneController');
    Route::post('/busdrawnone/pass',    'BusdrawnoneController@pass');
    Route::post('/busdrawnone/reject',  'BusdrawnoneController@reject');
    Route::resource('/busdrawdone',     'BusdrawdoneController');
    Route::resource('/busdrawreject',   'BusdrawrejectController');
    Route::resource('/agent',           'AgentController');
    Route::get('/agent/agentbankinfo/{id}', 'AgentController@agentbankinfo');
    Route::get('/agent/editpwd/{id}',   'AgentController@editpwd');
    Route::post('/changepwd',           'AgentController@changepwd');
    Route::get('/agent/editpayword/{id}','AgentController@editpayword');
    Route::post('/changepayword',       'AgentController@changepayword');
    Route::post('/agentUpdate',         'AgentController@update');
    Route::post('/agent_switch',        'AgentController@agent_switch');
    Route::post('/agent_islogin',       'AgentController@agent_islogin');
    Route::resource('/agentdrawnone',   'AgentdrawnoneController');
    Route::post('/agentdrawnone/pass',  'AgentdrawnoneController@pass');
    Route::post('/agentdrawnone/reject','AgentdrawnoneController@reject');
    Route::resource('/agentdrawdone',   'AgentdrawdoneController');
    Route::resource('/agentdrawreject', 'AgentdrawrejectController');
    Route::resource('/codeuser',        'CodeUserController');
    Route::post('/codeuserUpdate',      'CodeUserController@update');
    Route::post('/codeuser_isover',     'CodeUserController@codeuser_isover');

    Route::get('/codeuser/addqr/{id}',  'CodeUserController@addqr');
    Route::post('/codeaddqr',           'CodeUserController@codeaddqr');
    Route::get('/codeuser/tomsg/{id}',  'CodeUserController@tomsg');
    Route::post('/codeputmsg',          'CodeUserController@codeputmsg');
    Route::get('/codeuser/ownfee/{id}',  'CodeUserController@ownfee');
    Route::post('/codeuserfee',          'CodeUserController@codeuserfee');
    Route::get('/codeuser/logpwd/{id}',  'CodeUserController@logpwd');
    Route::post('/codenewpwd',          'CodeUserController@codenewpwd');
    Route::get('/codeuser/secondpwd/{id}', 'CodeUserController@secondpwd');
    Route::post('/codenewTwopwd',         'CodeUserController@codenewTwopwd');
    Route::get('/codeuser/zfpwd/{id}',  'CodeUserController@zfpwd');
    Route::post('/codenewpaypwd',         'CodeUserController@codenewpaypwd');

    Route::get('/codeownbill/own/{id}',  'CodeownbillController@own');
    Route::resource('/codeownbill',      'CodeownbillController');


    Route::resource('/coderakemoney',   'CoderakemoneyController');
    Route::post('/coderakemoneyUpdate', 'CoderakemoneyController@update');
    Route::resource('/codedrawnone',    'CodedrawnoneController');
    Route::post('/codedrawnone/pass',   'CodedrawnoneController@pass');
    Route::post('/codedrawnone/reject', 'CodedrawnoneController@reject');
    Route::resource('/codedrawdone',    'CodedrawdoneController');
    Route::resource('/codedrawreject',  'CodedrawrejectController');
    Route::resource('/recharge',        'RechargeController');
    Route::post('/recharge/enable',     'RechargeController@enable');
    Route::resource('/rechargelist',    'RechargelistController');
    Route::post('/rechargelist/enable', 'RechargelistController@enable');
    Route::resource('/billflow',        'BillflowController');
    Route::resource('/order',           'OrderController');
    Route::post('/order/budan', 'OrderController@budan');
    Route::post('/order/csbudan', 'OrderController@csbudan');
    Route::post('/order/sfpushfirst','OrderController@sfpushfirst');
    Route::resource('/buscount',        'BuscountController');
    Route::resource('/busbill',         'BusbillController');
    Route::resource('/agentcount',      'AgentcountController');
    Route::resource('/agentbill',       'AgentbillController');
    Route::resource('/codecount',       'CodecountController');
    Route::resource('/codebill',        'BillflowController');
    Route::resource('/busbank',         'BusbankController');
    Route::resource('/agentbank',       'AgentbankController');
});

Route::get('/phpinfo',function (Request $request){
   phpinfo();
});