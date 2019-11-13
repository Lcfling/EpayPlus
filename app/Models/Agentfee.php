<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/6
 * Time: 11:47
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Agentfee extends Model {
    protected  $table = 'agent_fee';
    public $timestamps = false;

    /**商户返佣调用
     * @param $tradeMoney 实际支付金额
     * @param $bussiness_code 商户号
     * @param $order_sn 平台订单号
     * @param $paycode 支付类型   1 微信  2支付宝
     */
    public static function bussiness_fy($tradeMoney,$bussiness_code,$order_sn,$paycode){
        if($busfee = Business::where('bussiness_code',$bussiness_code)->value('fee')){
            $brokerage= $tradeMoney * bcsub(1,$busfee,2);
            //修改商户账户信息
            Businesscount::where('bussiness_code',$bussiness_code)->increment('balance',$brokerage,['tol_sore'=>DB::raw("tol_sore + $brokerage")]);
           $agentfeeinfo = Agentfee::where('bussiness_code',$bussiness_code)->first();
           if($agentfeeinfo['agent1_id']){
               $feecha = bcsub($busfee,$agentfeeinfo['agent1_fee'],2);
               $agentbftable=Agentbillflow::getagentbftable($order_sn);
               if($feecha>0){
                    $score = $tradeMoney * $feecha;
                    $data =array(
                        'agent_id'=>$agentfeeinfo['agent1_id'],
                        'order_sn'=>$order_sn,
                        'score'=>$score,
                        'business_code'=>$bussiness_code,
                        'status'=>1,
                        'paycode'=>$paycode,
                        'remark'=>'支付返佣',
                        'creatime'=>time()
                    );
                    $agentbftable->insert($data);
                   //修改代理商账户信息
                    Agentcount::where('agent_id',$agentfeeinfo['agent1_id'])->increment('balance',$score,['tol_sore'=>DB::raw("tol_sore + $score"),'tol_brokerage'=>DB::raw("tol_brokerage + $score")]);
                }

                if($agentfeeinfo['agent2_id']){

                    $feecha2 =bcsub($agentfeeinfo['agent1_fee'],$agentfeeinfo['agent2_fee'],2);
                    if($feecha2>0){
                        $score2 = $tradeMoney * $feecha2;
                        $data2 =array(
                            'agent_id'=>$agentfeeinfo['agent2_id'],
                            'order_sn'=>$order_sn,
                            'score'=>$score2,
                            'business_code'=>$bussiness_code,
                            'status'=>1,
                            'paycode'=>$paycode,
                            'remark'=>'支付返佣',
                            'creatime'=>time()
                        );
                        $agentbftable->insert($data2);
                        //修改代理商账户信息
                        Agentcount::where('agent_id',$agentfeeinfo['agent1_id'])->increment('balance',$score2,['tol_sore'=>DB::raw("tol_sore + $score2"),'tol_brokerage'=>DB::raw("tol_brokerage + $score2")]);
                    }
                }else{
                    ajaxReturn('','无一级代理商!',0);
                }
           }else{
               ajaxReturn('','无一级代理商!',0);
           }
        }else{
            ajaxReturn('','商户不存在!',0);
        }

    }

    /**码商支付返佣
     * @param $tradeMoney 实际支付金额
     * @param $user_id 码商id
     * @param $bussiness_code 商户标识
     * @param $order_sn 订单号
     * @param $paycode 支付类型 1微信 2支付宝
     * @param $i 默认 1 返级用到
     * @param int $rate 下级费率
     * @return array|bool
     */
    public static function user_fy($tradeMoney,$user_id,$bussiness_code,$order_sn,$paycode,$i=1,$rate=0) {
        if ($i>15) {
            return false;
        }
        //自己的信息
        $userinfo=Users::where(array("user_id"=>$user_id))->first();
        $userrate = $userinfo['rate'];
        if ($paycode == 1) {
            //微信费率
            $score= bcsub($userrate,$rate,4)*$tradeMoney;
        } else {
            $score= bcsub($userrate,$rate,4)*$tradeMoney;
        }
        if($score<0 || $score==0) {
            return false;
        }
        $counttable=Accountlog::getcounttable($order_sn);
        $data =array(
            'user_id'=>$user_id,
            'order_sn'=>$order_sn,
            'score'=>$score,
            'business_code'=>$bussiness_code,
            'status'=>5,
            'paycode'=>$paycode,
            'remark'=>'支付返佣',
            'creatime'=>time()
        );
        file_put_contents('./userRebate.txt',"~~~~~~~~~~~~~~~第三方码商支付成功佣金发放~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./userRebate.txt',print_r($data,true).PHP_EOL,FILE_APPEND);
        $counttable->insert($data);
        //修改码商账户信息
        Userscount::where('user_id',$user_id)->increment('balance',$score,['tol_sore'=>DB::raw("tol_sore + $score"),'tol_brokerage'=>DB::raw("tol_brokerage + $score")]);
        $i++;
        if (empty($userinfo['pid'])) {
            return false;
        }
        $data=Agentfee::user_fy($tradeMoney,$userinfo['pid'],$bussiness_code,$order_sn,$paycode,$i,$userrate);
        return $data;
    }




}