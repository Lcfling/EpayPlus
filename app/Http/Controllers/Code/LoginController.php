<?php

namespace App\Http\Controllers\Code;

use App\Http\Controllers\Controller;
use App\Models\Users;

class LoginController extends Controller
{
    /**
     * 码商登录
     */
    public function login(){

        $account=(int)$_POST['account'];
        $password=htmlspecialchars($_POST['password']);
        $secret =$_POST['secret'];
        //判断用户名密码
        if($account!=""&&$password!=""&&$secret!=""){
            //查询用户信息
            $userinfo=Users::where(array("account"=>$account,"password"=>md5($password)))->first();

            if($userinfo){
                $ggkey =$userinfo['ggkey'];
                if(!$ggkey){
                    ajaxReturn(null,'您还未设置谷歌验证码!',0);
                }
                $status =verifyGooglex($secret,$ggkey);
                if(!$status){
                    ajaxReturn(null,'谷歌验证码失败!',0);
                }
                if ($userinfo['frozen'] == 1){
                    ajaxReturn(null,'此账号已被封禁!',0);
                }
//                if(md5($second_pwd)!=$userinfo['second_pwd']){
//                    ajaxReturn(null,'二级密码错误!',0);
//                }
                $token=md5(rand_string(6,1));
                Users::where(array("account"=>$account))->update(array("token"=>$token));
                $userinfo['token']=$token;
                ajaxReturn($userinfo,"成功");
            }else{
                ajaxReturn(null,'用户名或密码错误',0);
            }
        }else{
            ajaxReturn(null,'用户名或密码为空',0);
        }

    }



}
