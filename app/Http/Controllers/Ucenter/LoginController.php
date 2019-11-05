<?php
/**
 * 用户管理
 *
 * @author      fzs
 * @Time: 2017/07/14 15:57
 * @version     1.0 版本号
 */
namespace App\Http\Controllers\Ucenter;

use App\Http\Requests\StoreRequest;
use App\Models\Admin;
use App\Models\Erweima;
use App\Models\Imsi;
use App\Models\Log;
use App\Models\Order;
use App\Models\Role;
use App\Models\Ucenter;
use App\Models\User;
use App\Service\DataService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;



class LoginController extends BaseController
{

    /**
     * 用户登陆
     */
    public function login(){


        $account=(int)$_POST['account'];
        $password=htmlspecialchars($_POST['password']);
        $second_pwd=htmlspecialchars($_POST['second_pwd']);

        //判断用户名密码
        if($account!=""&&$password!=""&& $second_pwd!=""){
            //查询用户信息
            $userinfo=Ucenter::where(array("account"=>$account,"password"=>md5($password)))->first();
            if($userinfo){
                if ($userinfo['frozen'] == 1){
                    $this->ajaxReturn($userinfo,'此账号已被封禁!',0);
                }
                if(md5($second_pwd)!=$userinfo['second_pwd']){
                    $this->ajaxReturn(null,'二级密码错误!',0);
                }
                $token=md5($this->rand_string(6,1));
                Ucenter::where(array("account"=>$account))->update(array("token"=>$token));
                $userinfo['token']=$token;
                $this->ajaxReturn($userinfo,"成功");
            }else{
                $this->ajaxReturn(null,'用户名或密码错误',0);
            }
        }else{
            $this->ajaxReturn(null,'用户名或密码为空',0);
        }
    }



    /**
     * 设置二级密码
     */
    public function setsecondpwd(){

        $mobile=(int)$_POST['mobile'];
        $password=htmlspecialchars($_POST['pwd']);
        $second_pwd=htmlspecialchars($_POST['second_pwd']);
        $resecond_pwd=htmlspecialchars($_POST['resecond_pwd']);

        if(!$this->isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误！',0);
        }
        if($password==""){
            $this->ajaxReturn('','密码不能为空！',0);
        }
        if($second_pwd==""){
            $this->ajaxReturn('','二级密码不能为空！',0);
        }

        if($second_pwd!=$resecond_pwd){
            $this->ajaxReturn('','两次二级密码不相同！',0);
        }
        //判断用户是否存在
        $userInfo=Ucenter::where(array("account"=>$mobile))->first();

        if(empty($userInfo)){
            $this->ajaxReturn('','账户不存在！',0);
        }
        if($userInfo['second_pwd']!=""){
            $this->ajaxReturn('','已经设置过二级密码！',0);
        }

        if($userInfo['password']!=md5($password)){
            $this->ajaxReturn('','密码错误！',0);
        }

        if($userInfo['password']==md5($second_pwd)){
            $this->ajaxReturn('','原始密码不能与二级密码相同！',0);
        }

        $second_pwd=md5($second_pwd);
        if(Ucenter::where(array("account"=>$mobile))->update(array("second_pwd"=>$second_pwd))){
            $this->ajaxReturn('','操作成功！');
        }else{
            $this->ajaxReturn('','操作失败！',0);
        }
    }

    //  手机登陆
    public function mobilelogin(){
        $mobile=(int)$_POST['mobile'];
        $code=(int)$_POST['code'];

        if(!$this->isMobile($mobile)){
            $this->ajaxReturn(null,'手机号码格式错误！',0);
        }
        if (empty($code)){
            $this->ajaxReturn(null,'验证码为空！',0);
        }
        $redis=new redis();
        $Cachecode=$redis->get('login_code_'.$mobile);
        if($code!=$Cachecode){
            $this->ajaxReturn(null,'验证码错误！',0);
        }
        $userInfo=Ucenter::where(array("account"=>$mobile))->first();
        if(!empty($userInfo)){
            if ($userInfo['frozen'] == 1){
                $this->ajaxReturn($userInfo,'此账号已被封禁!',0);
            }
            $token=md5($this->rand_string(6,1));
            Ucenter::where(array("account"=>$mobile))->update(array("token"=>$token));
            $userinfo['token']=$token;
            $this->ajaxReturn($userInfo,'登陆成功！');
        }else{
            $this->ajaxReturn("",'账号不存在！',0);
        }
    }


    /**
     * 用户注册
     */
    public function mobile(){
        $mobile=(int)$_POST['mobile'];
        $code=htmlspecialchars($_POST['code']);
        $password=htmlspecialchars($_POST['pwd']);
        $repassword=htmlspecialchars($_POST['repwd']);

        if(!$this->isMobile($mobile)){
            $this->ajaxReturn(null,'手机号码格式错误',0);
        }
        if($password!=$repassword){
            $this->ajaxReturn(null,'密码不一致',0);
        }
        if(strlen($password)<6){
            $this->ajaxReturn(null,'密码不能小于6位',0);
        }


        //判断邀请码是否存在
        $codeinfo=Imsi::where(array("code"=>$code))->first();
        if (empty($codeinfo)){
            $this->ajaxReturn(null,'邀请码不存在',0);
        }

        if ($codeinfo['status'] == 1){
            $this->ajaxReturn(null,'邀请码已被占用'.$code,0);
        }
        //判断用户是否存在
        $userInfo=Ucenter::where(array("account"=>$mobile))->first();
        if(!empty($userInfo)){
            $this->ajaxReturn(null,'账号已存在！',0);
        }else{
            //不存在 入库用户信息
            $user_id=$this->insertUserInfo($mobile,$codeinfo,$password);
            if(empty($user_id)){
                $this->ajaxReturn(null,'注册失败！',0);
            }else{
                Imsi::where(array("code"=>$code))->update(array("bind_id"=>$user_id,"status"=>1,"zytime"=>time()));
                $user_info=Ucenter::where(array("account"=>$mobile))->first();
                $this->ajaxReturn($user_info,'注册成功！');
            }
        }
    }


    public function insertUserInfo($mobile,$codeinfo,$password){


        if($codeinfo["user_id"]==0||$codeinfo["user_id"]==""||$codeinfo["user_id"]==null)
            $codeinfo["user_id"]=0;

        $info['pid']=$codeinfo["user_id"];
        $info['shenfen']=$codeinfo["grade"];
        $info['account']=$mobile;
        $info['password']=md5($password);
        $info['token']=md5($this->rand_string(6,1));
        $info['money']=0;
        $info['imsi_num']=0;
        $info['frozen']=0;
        $info['take_status']=0;
        $info['reg_ip']=$info['last_ip']= $this->get_ip();
        $info['reg_time']=$info['last_time']=time();
        $info['mobile']=$mobile;
        $status=Ucenter::insert($info);
        return $status;
    }



}
