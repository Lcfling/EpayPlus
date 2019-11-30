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
    Route::get('/register',             'BindController@index');    //绑定谷歌验证码
    Route::post('/valAccount',          'BindController@checkAccount'); //效验账号是否存在
    Route::post('/valUser',             'BindController@checkUserLogin');//效验账号密码的真实性
    Route::post('/sendSMS',             'BindController@sendSMS');//发送验证码
    Route::post('/bindCode',            'BindController@bindCode');//绑定加效验
    Route::get('/login',                'LoginController@showLoginForm')->name('login');//登录
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
    Route::post('/bus_switch',          'BusinessController@bus_switch');

    Route::resource('/busdrawnone',     'BusdrawnoneController');//商户提现-审核
    Route::post('/busdrawnone/pass',    'BusdrawnoneController@pass');//通过
    Route::get('/busdrawnone/bohui/{id}',  'BusdrawnoneController@bohui');//驳回-原因页面
    Route::post('/busdrawnonereject',    'BusdrawnoneController@reject');//驳回

    Route::resource('/busdrawdone',     'BusdrawdoneController');//通过列表

    Route::resource('/busdrawreject',   'BusdrawrejectController');//驳回列表


    Route::resource('/agent',           'AgentController');//代理商
    Route::get('/agent/agentbankinfo/{id}', 'AgentController@agentbankinfo');//代理银行信息
    Route::get('/agent/editpwd/{id}',   'AgentController@editpwd');//代理登录密码
    Route::post('/changepwd',           'AgentController@changepwd');
    Route::get('/agent/editpayword/{id}','AgentController@editpayword');//代理支付密码
    Route::post('/changepayword',       'AgentController@changepayword');
    Route::post('/agentUpdate',         'AgentController@update');
    Route::post('/agent_switch',        'AgentController@agent_switch');//代理状态
    Route::post('/agent_islogin',       'AgentController@agent_islogin');//代理登陆

    Route::resource('/agentdrawnone',   'AgentdrawnoneController');//代理提现处理
    Route::post('/agentdrawnone/pass',  'AgentdrawnoneController@pass');
    Route::get('/agentdrawnone/bohui/{id}','AgentdrawnoneController@bohui');
    Route::post('/agentdrawnonereject',     'AgentdrawnoneController@reject');//驳回

    Route::resource('/agentdrawdone',   'AgentdrawdoneController');//代理提现通过列表
    Route::resource('/agentdrawreject', 'AgentdrawrejectController');//代理提现驳回列表




    Route::resource('/codeuser',        'CodeuserController');//码商列表
    Route::post('/codeuserUpdate',      'CodeuserController@update');//码商编辑
    Route::post('/codeuser_isover',     'CodeuserController@codeuser_isover');//状态开关
    Route::get('/codeuser/showinfo/{id}',  'CodeuserController@showinfo');//查看码商个人信息

    Route::get('/codeuser/addqr/{id}',  'CodeuserController@addqr');//码商增加二维码数量
    Route::post('/codeaddqr',           'CodeuserController@codeaddqr');
    Route::get('/codeuser/tomsg/{id}',  'CodeuserController@tomsg');//码商通知
    Route::post('/codeputmsg',          'CodeuserController@codeputmsg');
    Route::get('/codeuser/ownfee/{id}',  'CodeuserController@ownfee');//码商费率
    Route::post('/codeuserfee',          'CodeuserController@codeuserfee');
    Route::get('/codeuser/logpwd/{id}',  'CodeuserController@logpwd');//码商登录密码
    Route::post('/codenewpwd',          'CodeuserController@codenewpwd');
    Route::get('/codeuser/secondpwd/{id}', 'CodeuserController@secondpwd');//码商二级密码/已弃用
    Route::post('/codenewTwopwd',         'CodeuserController@codenewTwopwd');
    Route::get('/codeuser/zfpwd/{id}',  'CodeuserController@zfpwd');//码商支付密码
    Route::post('/codenewpaypwd',         'CodeuserController@codenewpaypwd');
    Route::get('/codeuser/shangfen/{id}',  'CodeuserController@shangfen');//码商上分
    Route::post('/codeaddscore',         'CodeuserController@codeaddscore');
    Route::get('/codeuser/xiafen/{id}',  'CodeuserController@xiafen');//码商下分
    Route::post('/codeoffscore',         'CodeuserController@codeoffscore');
    Route::get('/codeownbill/own/{id}',  'CodeownbillController@own');//码商个人流水
    Route::resource('/codeownbill',      'CodeownbillController');

    Route::resource('/coderakemoney',   'CoderakemoneyController');//码商激活佣金
    Route::post('/coderakemoneyUpdate', 'CoderakemoneyController@update');

    Route::resource('/codedrawnone',    'CodedrawnoneController');//码商提现处理
    Route::post('/codedrawnone/pass',   'CodedrawnoneController@pass');
    Route::get('/codedrawnone/bohui/{id}',  'CodedrawnoneController@bohui');//驳回-原因页面
    Route::post('/codedrawnonereject', 'CodedrawnoneController@reject');

    Route::resource('/codedrawdone',    'CodedrawdoneController');//码商提现通过列表

    Route::resource('/codedrawreject',  'CodedrawrejectController');//码商提现驳回列表


    Route::resource('/recharge',        'RechargeController');//充值信息
    Route::post('/rechargeUpdate',      'RechargeController@update');//码商编辑
    Route::post('/status_switch',       'RechargeController@status_switch');

    Route::resource('/rechargelist',    'RechargelistController');//充值列表处理
    Route::post('/rechargelist/pass', 'RechargelistController@pass');
    Route::post('/rechargelist/reject', 'RechargelistController@reject');

    Route::resource('/rechargedone',    'RechargedoneController');//充值通过列表
    Route::resource('/rechargereject',  'RechargerejectController');//充值驳回列表

    Route::resource('/billflow',        'BillflowController');//码商流水

    Route::resource('/order',           'OrderController');//订单 过期处理
    Route::post('/order/budan',         'OrderController@budan');//订单 补单

    Route::resource('/orderundo',           'OrderundoController');//订单 取消处理
    Route::post('/orderundo/csbudan',       'OrderController@csbudan');//订单 超时补单

    Route::resource('/orderdone',       'OrderdoneController');//订单成功处理
    Route::post('/order/sfpushfirst',   'OrderController@sfpushfirst');//订单 手动回调

    Route::resource('/orderfalse',      'OrderfalseController');//订单异常列表
    Route::post('/order/falsebudan',    'OrderController@falsebudan');//订单 手动回调
    Route::post('/order/jiedong',       'OrderController@jiedong');//订单 手动回调

    Route::resource('/orderlist',       'OrderlistController');//订单列表

    Route::resource('/buscount',        'BuscountController');//商户总账单
    Route::resource('/busdaycount',     'BusdaycountController');//商户日账单
    Route::resource('/busbill',         'BusbillController');//商户流水
    Route::resource('/agentcount',      'AgentcountController');//代理账单
    Route::resource('/agentbill',       'AgentbillController');//代理流水
    Route::resource('/codecount',       'CodecountController');//码商账单
    Route::resource('/codebill',        'BillflowController');//码商流水
    Route::resource('/busbank',         'BusbankController');//商户银行
    Route::resource('/agentbank',       'AgentbankController');//代理银行
    Route::resource('/qrcode',          'QrcodeController');//码商--二维码列表
    Route::resource('/datacount',       'DatacountController');//平台数据统计
    Route::resource('/daycount',        'DaycountController');//平台数据统计
});

Route::get('/phpinfo',function (Request $request){
   phpinfo();
});