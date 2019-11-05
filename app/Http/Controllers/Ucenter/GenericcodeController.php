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
use App\Models\Log;
use App\Models\Order;
use App\Models\Role;
use App\Models\Ucenter;
use App\Models\User;
use App\Service\DataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class GenericcodeController extends Controller
{


    /**
     * 账号激活
     */

    public function activate(){

        $user_id=$this->uid;
         //查询激活信息
        $jhmoney=DB::table("jhmoney")->first();

        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        if ($tolscore<$jhmoney->jhmoney){
            $this->ajaxReturn($tolscore,"账户余额不足!",0);
        }
        //查询用户信息
        $userinfo=Ucenter::where(array("user_id"=>$user_id))->first();
        if ($userinfo['jh_status'] == 1){
            $this->ajaxReturn($userinfo,"账号已激活!",0);
        }
        // 积分扣除
        $status=DB::table('account_log')->insert(
            array(
                'user_id'=>$user_id,
                'score'=>-$jhmoney->jhmoney,
                'status'=>7,
                'remark'=>'账户激活',
                'creatime'=>time()
            )
        );
        if ($status){
            //更改账户状态
            Ucenter::where(array("user_id"=>$user_id))->update(['jh_status' => 1]);
            D("Rebate")->jihuofy($userinfo['pid'],1);
            $this->ajaxReturn($status,"激活成功!");
        }else{
            $this->ajaxReturn($status,"激活失败!",0);
        }
    }

    /**
     * 激活佣金金额
     */
    public function jhmoney(){

        $jhmoney=DB::table("jhmoney")->first();
        $data['jhmoney']=$jhmoney->jhmoney;
        $this->ajaxReturn($jhmoney,"激活佣金!");
    }


    /**
     * 二维码开关
     */

    public function codekg(){
        $user_id=$this->uid;
        $erweima_id=(int)$_POST['erweima_id'];
        $redis=new redis();
        //查询二维码信息
        $erweimainfo=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->first();

        if ($erweimainfo['code_status']==0){
            $code_status=1;
            $msg="关闭二维码接单";
            //移除二维码队列
            $redis->lRem('erweimas'.$erweimainfo['type'].$user_id,$erweima_id,0);
        }else{
            $code_status=0;
            $msg="开启二维码接单";
            // 二维码存入用户缓冲
            $redis->rPush('erweimas'.$erweimainfo['type'].$user_id,$erweima_id);
        }
        // 修改二维码状态
        $savestatus=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->update(array("code_status"=>$code_status));

        $this->ajaxReturn($savestatus,$msg);
    }

}
