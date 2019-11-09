<?php
namespace App\Http\Controllers\Code;
use App\Models\Accountlog;
use App\Models\Erweima;
use App\Models\Jhmoney;
use App\Models\Users;
use App\Models\Userscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use \GatewayWorker\Lib\Gateway;
class GenericcodeController extends CommonController {
    /**
     * 账号激活
     */
    public function activate() {
        $user_id=$this->uid;
        $userinfo = $this->member;
        //查询激活信息
        $jhmoney=Jhmoney::first();
        // 查看用户积分
        $balance = Userscount::where(array('user_id'=>$user_id))->value('balance');
        if ($balance<$jhmoney['jhmoney']) {
            ajaxReturn($balance,"账户余额不足!",0);
        }
        if ($userinfo['jh_status'] == 1) {
            ajaxReturn($userinfo,"账号已激活!",0);
        }
        // 积分扣除
        $status=Accountlog::insert(
            array(
                'user_id'=>$user_id,
                'score'=>-$jhmoney->jhmoney,
                'status'=>7,
                'remark'=>'账户激活',
                'creatime'=>time()
            )
        );
        if ($status) {
            //更改账户状态
            Users::where(array("user_id"=>$user_id))->update(['jh_status' => 1]);
            Jhmoney::jihuofy($userinfo['pid'],1);
            ajaxReturn($status,"激活成功!");
        } else {
            ajaxReturn($status,"激活失败!",0);
        }
    }
    /**
     * 激活佣金金额
     */
    public function jhmoney() {
        $jhmoney=Jhmoney::first();
        $this->ajaxReturn($jhmoney,"激活佣金!");
    }
    /**
     * 二维码开关
     */
    public function codekg() {
        $user_id=$this->uid;
        $erweima_id=(int)$_POST['erweima_id'];
        //查询二维码信息
        $erweimainfo=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->first();
        if ($erweimainfo['code_status']==0) {
            $code_status=1;
            $msg="关闭二维码接单";
            //移除二维码队列
            Redis::lRem('erweimas'.$erweimainfo['type'].$user_id,$erweima_id,0);
        } else {
            $code_status=0;
            $msg="开启二维码接单";
            // 二维码存入用户缓冲
            Redis::rPush('erweimas'.$erweimainfo['type'].$user_id,$erweima_id);
        }
        // 修改二维码状态
        $savestatus=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->update(array("code_status"=>$code_status));
        ajaxReturn($savestatus,$msg);
    }
}