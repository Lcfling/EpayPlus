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
    Route::resource('/options',         'OptionController');//系统设置
    Route::post('/optionsUpdate',       'OptionController@update');
    Route::resource('/notices',         'NoticeController');//公告
    Route::post('/noticesUpdate',       'NoticeController@update');
    Route::resource('/callcenter',      'CallcenterController');//客服
    Route::post('/callcenterUpdate',    'CallcenterController@update');
    Route::resource('/business',        'BusinessController');//商户
    Route::get('/business/bankinfo/{id}', 'BusinessController@bankinfo');//商户银行信息
    Route::get('/business/buspwd/{id}', 'BusinessController@buspwd');//商户登录密码
    Route::post('/busnewpwd',           'BusinessController@busnewpwd');
    Route::get('/business/buspayword/{id}','BusinessController@buspayword');//商户支付密码
    Route::post('/busnewpayword',       'BusinessController@busnewpayword');
    Route::get('/business/busfee/{id}', 'BusinessController@busfee');//商户费率
    Route::post('/busnewfee',           'BusinessController@busnewfee');
    Route::post('/businessUpdate',      'BusinessController@update');
    Route::resource('/busdrawnone',     'BusdrawnoneController');//商户提现-审核
    Route::post('/busdrawnone/pass',    'BusdrawnoneController@pass');
    Route::post('/busdrawnone/reject',  'BusdrawnoneController@reject');
    Route::resource('/busdrawdone',     'BusdrawdoneController');//通过列表
    Route::resource('/busdrawreject',   'BusdrawrejectController');
    Route::resource('/agent',           'AgentController');//代理商
    Route::get('/agent/agentbankinfo/{id}', 'AgentController@agentbankinfo');//代理银行信息
    Route::get('/agent/editpwd/{id}',   'AgentController@editpwd');//代理登录密码
    Route::post('/changepwd',           'AgentController@changepwd');
    Route::get('/agent/editpayword/{id}','AgentController@editpayword');//代理支付密码
    Route::post('/changepayword',       'AgentController@changepayword');
    Route::post('/agentUpdate',         'AgentController@update');
    Route::post('/agent_switch',        'AgentController@agent_switch');//代理状态
    Route::post('/agent_islogin',       'AgentController@agent_islogin');//代理登陆
    Route::resource('/agentdrawnone',   'AgentdrawnoneController');//代理提现
    Route::post('/agentdrawnone/pass',  'AgentdrawnoneController@pass');
    Route::post('/agentdrawnone/reject','AgentdrawnoneController@reject');
    Route::resource('/agentdrawdone',   'AgentdrawdoneController');
    Route::resource('/agentdrawreject', 'AgentdrawrejectController');
    Route::resource('/codeuser',        'CodeUserController');//码商列表
    Route::post('/codeuserUpdate',      'CodeUserController@update');//码商编辑
    Route::post('/codeuser_isover',     'CodeUserController@codeuser_isover');//状态开关

    Route::get('/codeuser/addqr/{id}',  'CodeUserController@addqr');//码商增加二维码数量
    Route::post('/codeaddqr',           'CodeUserController@codeaddqr');
    Route::get('/codeuser/tomsg/{id}',  'CodeUserController@tomsg');//码商通知
    Route::post('/codeputmsg',          'CodeUserController@codeputmsg');
    Route::get('/codeuser/ownfee/{id}',  'CodeUserController@ownfee');//码商费率
    Route::post('/codeuserfee',          'CodeUserController@codeuserfee');
    Route::get('/codeuser/logpwd/{id}',  'CodeUserController@logpwd');//码商登录密码
    Route::post('/codenewpwd',          'CodeUserController@codenewpwd');
    Route::get('/codeuser/secondpwd/{id}', 'CodeUserController@secondpwd');//码商二级密码/已弃用
    Route::post('/codenewTwopwd',         'CodeUserController@codenewTwopwd');
    Route::get('/codeuser/zfpwd/{id}',  'CodeUserController@zfpwd');//码商支付密码
    Route::post('/codenewpaypwd',         'CodeUserController@codenewpaypwd');

    Route::get('/codeownbill/own/{id}',  'CodeownbillController@own');//码商个人流水
    Route::resource('/codeownbill',      'CodeownbillController');


    Route::resource('/coderakemoney',   'CoderakemoneyController');//码商激活佣金
    Route::post('/coderakemoneyUpdate', 'CoderakemoneyController@update');
    Route::resource('/codedrawnone',    'CodedrawnoneController');//码商提现
    Route::post('/codedrawnone/pass',   'CodedrawnoneController@pass');
    Route::post('/codedrawnone/reject', 'CodedrawnoneController@reject');
    Route::resource('/codedrawdone',    'CodedrawdoneController');
    Route::resource('/codedrawreject',  'CodedrawrejectController');
    Route::resource('/recharge',        'RechargeController');//充值信息
    Route::post('/recharge/enable',     'RechargeController@enable');
    Route::resource('/rechargelist',    'RechargelistController');//充值列表管理审核
    Route::post('/rechargelist/enable', 'RechargelistController@enable');
    Route::resource('/billflow',        'BillflowController');//码商流水
    Route::resource('/order',           'OrderController');//订单列表
    Route::post('/order/budan',         'OrderController@budan');//订单补单
    Route::post('/order/csbudan',       'OrderController@csbudan');//订单超时补单
    Route::post('/order/sfpushfirst',   'OrderController@sfpushfirst');//订单手动补单
    Route::resource('/buscount',        'BuscountController');//商户账单
    Route::resource('/busbill',         'BusbillController');//商户流水
    Route::resource('/agentcount',      'AgentcountController');//代理账单
    Route::resource('/agentbill',       'AgentbillController');//代理流水
    Route::resource('/codecount',       'CodecountController');//码商账单
    Route::resource('/codebill',        'BillflowController');//码商流水
    Route::resource('/busbank',         'BusbankController');//商户银行
    Route::resource('/agentbank',       'AgentbankController');//代理银行
    Route::resource('/qrcode',          'QrcodeController');//码商-二维码列表
    Route::resource('/datacount',       'DatacountController');//平台数据统计
});

Route::get('/phpinfo',function (Request $request){
   phpinfo();
});