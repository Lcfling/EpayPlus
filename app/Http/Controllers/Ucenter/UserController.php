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


class UserController extends Controller
{


    /**
     * 用户开始接单
     */
    public function start(){
        $user_id=$this->uid;

        $redis=new Redis();

        // 查询用户信息
       // $Userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        $Userinfo=Ucenter::where(array("user_id"=>$user_id))->first();

        if ($Userinfo['jh_status'] == 0){
            $this->ajaxReturn(null,"账号未激活!",0);
        }


        if ($Userinfo["take_status"]==1){

            //用户移除接单队列
            $redis->lRem("jiedans",$user_id,0);

           // D("Users")->where(array("user_id"=>$user_id))->save(array("take_status"=>0));
            Ucenter::where(array("user_id"=>$user_id))->update(['take_status' => 0]);
            $this->ajaxReturn(null,"关闭接单!");
        }

        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        if ($tolscore <=100000){
            $this->ajaxReturn($tolscore,"额度没有这么多啦!",0);
        }
        //查询用户二维码
        //$erweima=D("erweima_generic")->where(array("user_id"=>$user_id))->select();
        $erweima=Erweima::where(array("user_id"=>$user_id))->select();
        if (empty($erweima)){
            $this->ajaxReturn(null,"请先上传收款码!",0);
        }

        //用户入接单队列
        $redis->rPush('jiedans',$user_id);
      //  D("Users")->where(array("user_id"=>$user_id))->save(array("take_status"=>1));

        Ucenter::where(array("user_id"=>$user_id))->update(['take_status' => 1]);

        $this->ajaxReturn(null,"开始成功!");

    }


    /**
     * 码商进行抢单
     */
    public function qiangdan(){

        $user_id=$this->uid;   //用户
        $order_id=(int)$_POST['order_id']; //订单号



        //获取订单信息
       // $order_info= D("order")->where(array("id"=>$order_id))->find();
        $order_info=Order::where(array("id"=>$order_id))->first();


        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        $yue=$tolscore/100-1000;
        if ($yue<$order_info['tradeMoney']/100){
            $this->ajaxReturn(null,"用户金额不足!",0);
        }


        //$userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        $userinfo=Ucenter::where(array("user_id"=>$user_id))->first();


        if ($userinfo['take_status'] == 0){
            $this->ajaxReturn(null,"请先开启接单状态!",0);
        }


        $data=Erweima::where(array("user_id"=>$user_id,"status"=>0))->first();
        if (empty($data)){
            $this->ajaxReturn(null,"请先上传二维码!",0);
        }

        $redis=new Redis();

        // 取出队列二维码
        $erweima_id=$redis->LPOP("erweimas".$order_info['payType'].$user_id);
        if(!$erweima_id){
            $this->ajaxReturn('error40004','没有此类型支付码!'.$erweima_id,0);
        }


        //二维码归队
        $Users=new Ucenter();
        $Users->Genericlist($user_id,$order_info['payType'],$erweima_id);

        $order_time=$redis->get("ordertime_".$erweima_id.$order_info['tradeMoney']);
        if (  $order_time+600>time()){
            $this->ajaxReturn(null,"订单已被抢",0);
        }

        if($order_info['status'] !=0){
            $this->ajaxReturn(null,"订单已被抢",0);
        }


        //获取二维码信息
       // $erweimainfo=D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id,"type"=>$order_info['payType']))->find();

        $erweimainfo=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id,"type"=>$order_info['payType']))->first();


        if($erweimainfo['status'] == 1){
            $redis->lRem("erweimas".$order_info['payType'].$user_id,$erweima_id,0);
            $this->ajaxReturn('error40004','支付码已被删除!'.$erweima_id,0);
        }

        if(!$erweimainfo){
            $redis->lRem("erweimas".$order_info['payType'].$user_id,$erweima_id,0);
            $this->ajaxReturn('error40004','没有此码数据!'.$erweima_id,0);
        }


        if($erweimainfo['code_status'] == 1){
            $this->ajaxReturn('error40004','支付码关闭中!'.$erweima_id,0);
        }


        //取出订单队列
        $order_id=$redis->LPOP("order_id_".$order_id);

        if (!$order_id>0 || empty($order_id)){
            $this->ajaxReturn(null,"订单已被抢!",0);
        }

        //抢单成功
//        $logdata =array(
//            'user_id'=>$user_id,
//            'order_id'=>$order_info['order_sn'],
//            'score'=>-$order_info["tradeMoney"],
//            'erweima_id'=>$erweima_id,
//            'business_code'=>$order_info['business_code'],
//            'out_uid'=>$order_info["out_uid"],
//            'status'=>3,
//            'payType'=>$order_info["payType"],
//            'remark'=>'冻结中',
//            'creatime'=>time()
//        );
//        D('Account_log')->add($logdata);


        $status=DB::table('users')->insert(
            array(
                'user_id'=>$user_id,
                'order_id'=>$order_info['order_sn'],
                'score'=>-$order_info["tradeMoney"],
                'erweima_id'=>$erweima_id,
                'business_code'=>$order_info['business_code'],
                'out_uid'=>$order_info["out_uid"],
                'status'=>3,
                'payType'=>$order_info["payType"],
                'remark'=>'冻结中',
                'creatime'=>time()
            )
        );
        if (!$status){
            $this->ajaxReturn(null,"订单已被抢!",0);
        }


        //  更改订单状态
       // D("order")->where(array("id"=>$order_id))->save(array("user_id"=>$user_id,"erweima_id"=>$erweima_id));
        order::where(array("id"=>$order_id))->update(array("user_id"=>$user_id,"erweima_id"=>$erweima_id));

        //发送被抢订单信息
        $this->sendnotify($order_info,2);
        $redis->set("ordertime_".$erweima_id.$order_info['tradeMoney'],time());

        //发送被抢订单信息
        $ourdercount=Order::where(array("user_id"=>$user_id,"status"=>0))->count();


        //获取订单信息
       // $order_infos= D("order")->where(array("id"=>$order_id))->find();
        $order_infos=Order::where(array("id"=>$order_id))->first();
        $this->senduidnotify($order_infos,3,$ourdercount);

        $this->ajaxReturn(null,"抢单成功!");
    }


    /**
     * 用户列表
     */
    public function index()
    {
        $user_id=11863;

        $redie=new redis();
        $redie->set("user_id".$user_id,$user_id);
        $userid=$redie->get("user_id".$user_id);
        echo $userid;
        die();

        $Usersinfos=Ucenter::where(array("user_id"=>$user_id))->first();
        echo  $Usersinfos->user_id;

       $Usersinfos=json_decode($Usersinfos,true);
        var_dump($Usersinfos);
        die();



        $Eeweimainfo=Erweima::where(array("user_id"=>$user_id))->first();

        echo $Eeweimainfo->home;
        die($Eeweimainfo['name']);

//        print_r($Eeweimainfo);


        Ucenter::where(array("user_id"=>$user_id))->update(['take_status' => 1]);

        die();


        $Ucenter=new Ucenter();
        $Usersinfo=$Ucenter->Userinfo($user_id);
        print_r($Usersinfo);

    }


    /**
     * 记录用户在线时间
     */
    public function is_status(){

        $user_id=$this->uid;
        $redis=new redis();
        //存入用户最后在线时间
        $redis->set("jiedan_status".$user_id,time());
    }

    /**
     * 二维码信息
     */
    public function erweima_info(){

        $user_id=$this->uid;
        $erweima_id=(int)$_POST['erweima_id'];


        // 分配记录
        $erweima_log=D("erweima_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id))->order(array('id'=>'desc'))->select();


        foreach ($erweima_log as &$v){
            $v['score']=$v['score']/100;
            $v['creatime']=date("Y-m-d : H:i:s",$v['creatime']);
        }

        //跑分记录
        $account_log=D("account_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>2))->order(array('id'=>'desc'))->select();
        foreach ($account_log as &$v){
            $v['score']=$v['score']/100;
            $v['creatime']=date("Y-m-d : H:i:s",$v['creatime']);
        }

        //当前剩余
        $erweima=D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id))->find();

        //已跑总额
        $sumscore=D("account_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>2))->field("sum(score) as score")->select();


        $data['fenpei']=$erweima_log;
        $data['paofen']=$account_log;
        $data['shengyu']=$erweima['limit'];
        $data['yipao']=$sumscore[0]['score']/100;

        $this->ajaxReturn($data,"二维码信息!");

    }


    /**
     * 分配额度
     */
    public function  addscore(){
        $user_id=$this->uid;
        $score=(int)$_POST['score'];
        $erweima_id=(int)$_POST['erweima_id'];

        $erweima=D("erweima_generic");

        // 二维码添加积分
        $erweima->where(array("user_id"=>$user_id,"id"=>$erweima_id))->setInc("limits",$score);

        //增加积分记录
        $erweima_log=D("erweima_log");
        $data['user_id']=$user_id;
        $data['erweima_id']=$erweima_id;
        $data['score']=$score*100;
        $data['creatime']=time();
        $id=$erweima_log->add($data);
        if ($id){
            $this->ajaxReturn($id,"分配成功!");
        }else{
            $this->ajaxReturn($id,"分配失败!",0);
        }
    }

    /**
     * @param $orderinfo
     * @param $type
     * @param $ordercount
     * 发送数据
     */
    private function senduidnotify($orderinfo,$type,$ordercount)
    {

        Gateway::$registerAddress = '127.0.0.1:1238';


        $data=array(
            'ordercount'=>$ordercount,
            'type'=>$type,
            'data'=>array(
                'order_id'=>$orderinfo['id'],
                'payType'=>$orderinfo['payType'],
                'tradeMoney'=>$orderinfo['tradeMoney'],
                'order_sn'=>$orderinfo['order_sn'],
                'time'=>$orderinfo['creatime']),
            'home'=>$orderinfo['home']
        );

        $data=json_encode($data);
        Gateway::sendToUid($orderinfo['user_id'],$data);

    }

    /**
     * @param $orderinfo
     * @param $type
     * 发送数据
     */
    private function sendnotify($orderinfo,$type)
    {

        Gateway::$registerAddress = '172.18.20.112:1238';

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
