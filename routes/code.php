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


//登陆模块
Route::group(['namespace'  => "Code"], function () {

    /**
     * IndexController
     */
    Route::post('/index',                             'IndexController@index');
    Route::post('/welcome',                           'IndexController@welcome');
    Route::post('/index/update',                      'IndexController@update');//检测更新

    /**
     *LoginController
     */
    Route::post('/login/login',                       'LoginController@login');//码商登录
    Route::post('/login/mobile',                      'LoginController@mobile');//注册
    Route::post('/login/sendcode',                    'LoginController@sendcode');//短信登录发送验证码
    Route::post('/login/mobilelogin',                 'LoginController@mobilelogin');//手机验证码登陆
    /**
     * MycenterController
     */
    Route::post('/Mycenter/getaccount',               'MycenterController@getaccount');//我的账户
    Route::post('/Mycenter/withdraw_check',           'MycenterController@withdraw_check');//提现校验
    Route::post('/Mycenter/withdraw',                 'MycenterController@withdraw');//提现
    Route::post('/Mycenter/getuserinfo',              'MycenterController@getuserinfo');//个人信息
    Route::post('/Mycenter/real_name',                'MycenterController@real_name');//实名认证
    Route::post('/Mycenter/withdraw_list',            'MycenterController@withdraw_list');//提现列表
    Route::post('/Mycenter/recharge_list',            'MycenterController@recharge_list');//充值列表
    Route::post('/Mycenter/setpass',                  'MycenterController@setpass');//设置支付密码
    Route::post('/Mycenter/sendcode',                 'MycenterController@sendcode');//获取支付验证码
    Route::post('/Mycenter/verification',             'MycenterController@verification');//支付验证验证码
    Route::post('/Mycenter/logpasssendcode',          'MycenterController@logpasssendcode');//获取修改登录密码验证码
    Route::post('/Mycenter/logpassverify',            'MycenterController@logpassverify');//登录密码验证验证码
    Route::post('/Mycenter/setlogpass',               'MycenterController@setlogpass');//设置登录密码
    Route::post('/Mycenter/setsecondpwd',             'MycenterController@setsecondpwd');//设置二级登录密码
    Route::post('/Mycenter/createcode',               'MycenterController@createcode');//生成推广码
    Route::post('/Mycenter/agent_list',               'MycenterController@agent_list');//下级代理
    Route::post('/Mycenter/issuecode',                'MycenterController@issuecode');//下发邀请码
    Route::post('/Mycenter/changepro',                'MycenterController@changepro');//修改微信分润
    Route::post('/Mycenter/changepros',               'MycenterController@changepros');//修改支付宝分润
    Route::post('/Mycenter/qrcode',                   'MycenterController@qrcode');//二维码列表
    Route::post('/Mycenter/qrcodedel',                'MycenterController@qrcodedel');//二维码删除
    Route::post('/Mycenter/codelist',                 'MycenterController@codelist');//邀请码记录
    Route::post('/Mycenter/prosavelist',              'MycenterController@prosavelist');//修改分润
    Route::post('/Mycenter/acountlist',               'MycenterController@acountlist');//资金记录
    Route::post('/Mycenter/home',                     'MycenterController@home');//城市选择
    Route::post('/Mycenter/qrcodes',                  'MycenterController@qrcodes');//二维码删除列表
    Route::post('/Mycenter/saveuserinfo',             'MycenterController@saveuserinfo');//修改银行卡信息
    Route::post('/Mycenter/getGoogle2FA',             'MycenterController@getGoogle2FA');//获取谷歌秘钥和绑定二维码地址
    Route::post('/Mycenter/Google2FAsendcode',        'MycenterController@Google2FAsendcode');//获取查看谷歌验证的手机验证码

    Route::post('/orderym/kfnotifyurl',               'OrderymController@kfnotifyurl');//app支付成功回调
    Route::post('/orderym/kuaifupay',                 'OrderymController@kuaifupay');//第三方调取支付
    Route::post('/orderym/orderinfo',                 'OrderymController@orderinfo');//订单状态检测
    /**
     * 定时器
     */
    Route::post('/Timernotify/sfpushfirst',           'TimernotifyController@sfpushfirst');//第一次 异步回调
    Route::post('/Timernotify/sfpushsecond',          'TimernotifyController@sfpushfirst');//第二次 异步回调
    Route::post('/Timernotify/sfpushthird',           'TimernotifyController@sfpushfirst');//第三次 异步回调
    Route::get('/Timernotify/setstale',               'TimernotifyController@setstale');//订单10分钟更改为过期
    Route::get('/Timernotify/orderunfreeze',          'TimernotifyController@orderunfreeze');//过期订单解冻并返回跑分 更改订单为订单取消
    Route::get('/Timernotify/bussiness_fy',           'TimernotifyController@bussiness_fy');//商户返佣
    Route::get('/Timernotify/user_fy',                'TimernotifyController@user_fy');//码商返佣


    /**
     * OrderjdController
     */
    Route::post('/Orderjd/orderjd_list',            'OrderjdController@orderjd_list');//接单列表
    Route::post('/orderjd/orderjd_listinfo',        'OrderjdController@orderjd_listinfo');//获取订单详细信息
    Route::post('/Orderjd/savesk_status',           'OrderjdController@savesk_status');//订单列表补单
    Route::post('/Orderjd/ordering',                'OrderjdController@ordering');//进行中的抢单
    Route::post('/orderjd/chongzhi',                'OrderjdController@chongzhi');//上传充值凭证
    Route::post('/orderjd/chongzhirecord',          'OrderjdController@chongzhirecord');//提交记录
    Route::post('/orderjd/chongzhiinfo',            'OrderjdController@chongzhiinfo');//充值信息

    /**
     * ZfnoticeController
     */
    Route::post('/Zfnotice/index',                  'ZfnoticeController@index');//获取通告
    Route::post('/Zfnotice/message',                'ZfnoticeController@message');//获取消息
    Route::post('/Zfnotice/setifread',              'ZfnoticeController@setifread');//已读消息
    Route::post('/zfnotice/kefu',                   'ZfnoticeController@kefu');//客服
    Route::post('/Zfnotice/getnotice',              'ZfnoticeController@getnotice');//邀请码记录

    /**
     * GenericcodeController
     */
    Route::post('/genericcode/shangma',              'GenericcodeController@shangma');//上传图片
    Route::post('/Genericcode/codekg',               'GenericcodeController@codekg');//二维码开关
    Route::post('/genericcode/activate',             'GenericcodeController@activate');//激活账户
    Route::post('/Genericcode/jhmoney',              'GenericcodeController@jhmoney');//激活账户的金额

    /**
     * UserController
     */
    Route::post('/user/start',                    'UserController@start');//开启接单
    Route::post('/user/is_status',                'UserController@is_status');//判断是否在线
    Route::post('/user/erweima_info',             'UserController@erweima_info');//二维码历史记录
    Route::post('/user/erweima_list',             'UserController@erweima_list');//二维码金额列表
    Route::post('/user/qiangdan',                 'UserController@qiangdan');//抢单


});