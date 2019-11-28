<?php
/**
 * 用户管理
 *
 * @author      fzs
 * @Time: 2017/07/14 15:57
 * @version     1.0 版本号
 */
namespace App\Http\Controllers\Code;

use App\Models\Accountlog;
use App\Models\Erweima;
use App\Models\Order;
use App\Models\Users;
use App\Models\Userscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use \GatewayWorker\Lib\Gateway;
class UserController extends CommonController {

    /**
     * 用户开始接单
     */
    public function start(Request $request) {
        if($request->isMethod('post')) {
            $user_id=$this->uid;
            // 查询用户信息
            $Userinfo=Users::where(array("user_id"=>$user_id))->first();
            if ($Userinfo['jh_status'] == 0) {
                ajaxReturn(null,"账号未激活!",0);
            }

            if ($Userinfo["take_status"]==1) {
                //用户移除接单队列
                Redis::lRem("jiedans",0,$user_id);
                Users::where(array("user_id"=>$user_id))->update(['take_status' => 0]);
                ajaxReturn(null,"关闭接单!");
            }
            if (!$Userinfo['home']) {
                ajaxReturn(null,"未设置接单地区!",0);
            }
            // 查看用户积分余额
            $balance = Userscount::onWriteConnection()->where('user_id',$user_id)->value('balance');
            if ($balance <=100000) {
                ajaxReturn($balance,"额度没有这么多啦!",0);
            }
            //查询用户二维码
            $erweima=Erweima::where(array("user_id"=>$user_id))->first();
            if (!$erweima) {
                ajaxReturn(null,"请先上传收款码!",0);
            }
            //用户入接单队列
            Redis::rPush('jiedans',$user_id);
            Users::where(array("user_id"=>$user_id))->update(['take_status' => 1]);
            ajaxReturn(null,"开始成功!");
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 码商进行抢单
     */
    public function qiangdan(Request $request) {
        if($request->isMethod('post')) {
            $user_id=$this->uid;
            $userinfo =$this->member;
            //用户
            $order_sn=$_POST['order_sn'];
            //订单号
            if ($userinfo['take_status'] == 0) {
                ajaxReturn(null,"请先开启接单状态!",0);
            }
            //获取订单信息
            $orderjson=Redis::get('orderrecord_'.$order_sn);
            if(!$orderjson){
                ajaxReturn(null,"订单已不存在,请刷新当前页面!",0);
            }
            $order_info =json_decode($orderjson,true);
            $tradeMoney = $order_info['tradeMoney'];

            // 取出队列二维码
            $erweima_id=Redis::LPOP("erweimas".$order_info['payType'].$user_id);
            if(!$erweima_id) {
                ajaxReturn('error40004','没有此类型支付码!'.$erweima_id,0);
            }
            //二维码归队
            Users::Genericlist($user_id,$order_info['payType'],$erweima_id);
            $order_time=Redis::get("ordertime_".$erweima_id.$order_info['tradeMoney']);
            if ( !empty($order_time) &&$order_time+600>time()){
                ajaxReturn(null,"已有相同金额订单!",0);
            }
            $erweimajson=Redis::get('erweimainfo_'.$erweima_id);
            $erweimainfo = json_decode($erweimajson,true);
            if(!$erweimainfo) {
                ajaxReturn('error40004','无此二维码信息!'.$erweima_id,0);
            }
            if($erweimainfo['status'] ==1) {
                Redis::lRem("erweimas".$order_info['payType'].$user_id,0,$erweima_id);
                ajaxReturn('error40004','支付码已被删除!'.$erweima_id,0);
            }
            if($erweimainfo['code_status'] == 1) {
                ajaxReturn('error40004','支付码关闭中!'.$erweima_id,0);
            }
            // 查看用户积分余额
            $balance =Userscount::onWriteConnection()->where('user_id',$user_id)->value('balance');
            $yue=bcsub($balance/100,1000,2);
            if ($yue<$tradeMoney/100) {
                ajaxReturn(null,"账户余额不足!",0);
            }
            //取出订单队列
            $order_id=Redis::LPOP("order_id_".$order_sn);
            if (!$order_id>0 || empty($order_id)) {
                ajaxReturn(null,"订单已被抢!",0);
            }

            DB::beginTransaction();
            try {
                // 查看用户积分余额
                $balance =Userscount::onWriteConnection()->where('user_id',$user_id)->lockForUpdate()->value('balance');
                $yue=bcsub($balance/100,1000,2);
                if ($yue<$tradeMoney/100) {
                    DB::rollBack();
                    ajaxReturn(null,"账户余额不足!",0);
                }
                $moneystatus =Userscount::where('user_id',$user_id)->decrement('balance',$tradeMoney,['freeze_money'=>DB::raw("freeze_money + $tradeMoney")]);
                if(!$moneystatus){
                    DB::rollBack();
                    ajaxReturn(null,"抢单失败!",0);
                }
                $counttable = Accountlog::getcounttable($order_sn);
                $status=$counttable->insert(
                    array(
                        'user_id'=>$user_id,
                        'order_sn'=>$order_sn,
                        'score'=>-$tradeMoney,
                        'erweima_id'=>$erweima_id,
                        'business_code'=>$order_info['business_code'],
                        'status'=>3,
                        'payType'=>$order_info["payType"],
                        'remark'=>'资金冻结',
                        'creatime'=>time()
                    )
                );
                if (!$status) {
                    DB::rollBack();
                    ajaxReturn(null,"抢单失败!",0);
                }else{
                    $order =Order::getordersntable($order_sn);
                    //  更改订单状态
                    $orderstatus = $order->where(array("order_sn"=>$order_sn))->update(array("user_id"=>$user_id,"erweima_id"=>$erweima_id));
                    if($orderstatus){
                        DB::commit();
                    }else{
                        DB::rollBack();
                        ajaxReturn(null,"抢单失败!",0);
                    }
                    Redis::set("ordertime_".$erweima_id.$order_info['tradeMoney'],time());
                    //发送被抢订单信息
                    $this->sendnotify($order_info,2);
                    //发送被抢订单信息
                    $ourdercount=Redis::incr('order_qd_'.$user_id);
                    //type3推送
                    $this->senduidnotify($order_info,3,$ourdercount,$user_id);
                    //返回信息
                    ajaxReturn(null,"抢单成功!");
                }
            }
            catch (Exception $e) {
                // 数据回滚, 当try中的语句抛出异常。
                DB::rollBack();
                ajaxReturn(null,"抢单失败!",0);
            }

        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 记录用户在线时间
     */
    public function is_status() {
        $user_id=$this->uid;
        //存入用户最后在线时间
        Redis::set("jiedan_status".$user_id,time());
    }
    /**
     * 二维码信息
     */
    public function erweima_info(Request $request) {
        if($request->isMethod('post')) {
            $user_id=$this->uid;
            $erweima_id=(int)$_POST['erweima_id'];
            $order = Order::getordertable();
            //跑分记录
            $account_log=$order->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>1))->orderBy('creatime','desc')->get();
            foreach ($account_log as &$v) {
                $v['score']=$v['score']/100;
                $v['creatime']=date("Y-m-d : H:i:s",$v['creatime']);
            }
            //当前剩余
            $erweima=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->first();
            //已跑总额
            $sumscore=$order->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>1))->sum('tradeMoney');
            $data['paofen']=$account_log;
            $data['shengyu']=$erweima['limit'];
            $data['yipao']=$sumscore/100;
            ajaxReturn($data,"二维码信息!");
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * @param $orderinfo
     * @param $type
     * @param $ordercount
     * 发送数据
     */
    private function senduidnotify($orderinfo,$type,$ordercount,$user_id) {
        $address = Redis::get('push_map');
        Gateway::$registerAddress = $address.':1236';
        $data=array(
            'ordercount'=>$ordercount,
            'type'=>$type,
            'data'=>array(
                'payType'=>$orderinfo['payType'],
                'tradeMoney'=>$orderinfo['tradeMoney'],
                'order_sn'=>$orderinfo['order_sn'],
                'time'=>$orderinfo['creatime']),
            'home'=>$orderinfo['home']
        );
        $data=json_encode($data);
        Gateway::sendToUid($user_id,$data);
    }
    /**
     * @param $orderinfo
     * @param $type
     * 发送数据
     */
    private function sendnotify($orderinfo,$type) {
        $address = Redis::get('push_map');
        Gateway::$registerAddress = $address.':1236';
        $data=array(
            'ordercount'=>1,
            'type'=>$type,
            'data'=>array(
                'order_id'=>$orderinfo['id'],
                'payType'=>$orderinfo['payType'],
                'tradeMoney'=>$orderinfo['tradeMoney'],
                'order_sn'=>$orderinfo['order_sn'],
                'time'=>$orderinfo['creatime']),
            'home'=>$orderinfo['home']
        );
        $data=json_encode($data,true);
        Gateway::sendToAll($data);
    }

}