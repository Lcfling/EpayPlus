<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/3
 * Time: 17:39
 */
namespace App\Http\Controllers\Code;
use App\Models\Business;
use App\Models\Erweima;
use App\Models\Order;
use App\Models\Orderrecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;
use \GatewayWorker\Lib\Gateway;

class OrderymController extends Controller {
    /**
     * 获取商户秘钥 唯一识别码
     */
    public function getbusiness() {
        $business = D('business');
        $businessinfo=$business->where(array('id'=>1))->find();
        $data =array(
            'business_code'=>$businessinfo['business_code'],
            'accessKey'=>$businessinfo['accessKey']
        );
        ajaxReturn($data,'请求成功!',1);
    }
    /**
     * 第三方调支付
     */
    public function kuaifupay() {
        $datas =$_POST;
        file_put_contents('./businesspost.txt',"~~~~~~~~~~~~~post数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',print_r($datas,true).PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',"~~~~~~~~~~~~~business_code".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',print_r($datas['business_code'],true).PHP_EOL,FILE_APPEND);
        $sign=htmlspecialchars($datas['sign']);
        $business_code=htmlspecialchars($datas['business_code']);
        if(empty($business_code)) {
            ajaxReturn('error40002','商户号不能为空!',0);
        }

        //商户号 不参与签名
        $out_order_sn = htmlspecialchars($datas['out_order_sn']);
        //商户订单号
        $type =(int)$datas['payType'];
        $codeType =(int)$datas['codeType'];
        unset($datas['sign']);
        unset($datas['business_code']);
        $time = time();
        $weeksuf =computeWeek($time,false);
        $Order = Order::getordertable($weeksuf);
        $where['business_code']=$business_code;
        $businessinfo=Business::where($where)->first();
        if(empty($businessinfo)) {
            ajaxReturn('error40003','商户未启用!',0);
        }
        if ($businessinfo['status'] == 2) {
            ajaxReturn('error40000','商户已停止!',0);
        }
        if (!is_numeric($datas['tradeMoney'])) {
            ajaxReturn('error40006','订单金额有误!',0);
        }
        if ($type != 1 && $type != 2) {
            ajaxReturn('error40007','支付类型无效!',0);
        }
        if ($codeType != 2) {
            ajaxReturn('error40008','支付类型无效!',0);
        }
        if ($orderlist = $Order->where(array('out_order_sn'=>$out_order_sn,'business_code'=>$business_code,'status'=>[0,2]))->first()) {
            ajaxReturn('error','已有此订单信息!',0);
        }
        if( $sign!=$this->getSignK($datas,$businessinfo['accessKey'])) {
            file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~平台sign".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
            $psign = $this->getSignK($datas,$businessinfo['accessKey']);
            file_put_contents('./sign.txt',print_r($psign,true).PHP_EOL,FILE_APPEND);
            file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~平台商户sign".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
            file_put_contents('./sign.txt',print_r($sign,true).PHP_EOL,FILE_APPEND);

            ajaxReturn('error','签名错误!',0);
        }
        $order_sn = getrequestId($type,$business_code,$weeksuf);
        $time = time();
        //保存商户订单记录
        $data =array(
            'out_order_sn'=>$datas["out_order_sn"],
            'order_sn'=>$order_sn,
            'payType'=>$datas["payType"],
            'tradeMoney'=>$datas["tradeMoney"],
            'business_code'=>$business_code,
            'creatime'=>$time,
            'notifyUrl'=>$datas['notifyUrl'],
        );
        $order_id = $Order->insertGetId($data);
        if($order_id) {
            // 发送订单数据到码商客户端
            $data['id']=$order_id;
            //订单入队
            Redis::set('order_sn_'.$order_id,$order_sn);
            Redis::set('order_id_'.$order_sn,$order_id);
            //保存商户订单记录
            $recorddata =array(
                'order_sn'=>$order_sn,
                'payType'=>$datas["payType"],
                'tradeMoney'=>$datas["tradeMoney"],
                'submeter_name'=>'order_'.$weeksuf,
                'creatime'=>$time,
            );
            Orderrecord::insert($recorddata);
            $this->getcomonerweimaurl($order_id);
        } else {
            ajaxReturn('error40005','订单生成失败!',0);
        }
    }

    /**获取订单信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderinfo(Request $request) {
        if($request->isMethod('post')){
            $order_id=(int)$_POST['order_id'];
            $home=htmlspecialchars($_POST['home']);
            $order =  $this->getordertable($order_id);
            if (empty($order_id) ) {
                ajaxReturn(null,'请求信息错误!',0);
            }
            if (empty($home) ) {
                ajaxReturn(null,'请求信息错误!',0);
            }
            if($order_info= $order->where(array("id"=>$order_id,'status'=>0))->first()){
                if($order_info['is_send']==1){
                    if ( !$order_info['user_id']>0 || empty($order_info['user_id'])) {
                        ajaxReturn(null,'暂无人接单!',0);
                    }
                    $erweima_info=Erweima::where(array("id"=>$order_info['erweima_id']))->first();
                    $data=array(
                        "erweimaurl"=>"http://47.111.110.1:8555".$erweima_info['erweima'],
                        "order_id"=>$order_id,
                        "user_id"=>$erweima_info['user_id'],
                        "gptime"=>$order_info['creatime']+600,
                        "type"=>$order_info['payType'],
                        "business_code"=>$order_info['business_code'],
                        "payMoney"=>$order_info['payMoney']/100
                    );
                    ajaxReturn($data,"订单详情!",1);
                }else{
                    $order_info['home']=$home;
                    $this->sendnotify($order_info,1);
                    $order->where(array("id"=>$order_id,'status'=>0))->update(array('is_send'=>1,'home'=>$home));
                }
            }elseif($order->where(array('id'=>$order_id,'status'=>2))->first()){
                ajaxReturn('','订单已过期!',0);
            }elseif($order->where(array('id'=>$order_id,'status'=>3))->first()){
                ajaxReturn('','订单已被取消!',0);
            }elseif($order->where(array('id'=>$order_id,'status'=>1))->first()){
                ajaxReturn('','已支付成功!',0);
            }else{
                ajaxReturn(null,'订单不存在!',0);
            }

        }else{
            ajaxReturn('','请求数据异常!',0);
        }

    }
    /**获取通用码支付页面
     * @param $erweimaurl
     */
    private function getcomonerweimaurl($order_id) {
        $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zfcmfirst.html?order_id='.$order_id;
        ajaxReturn('OK',$qrurl,1001);
        //输出支付url
    }

    private function sendnotify($orderinfo,$type) {
        Gateway::$registerAddress = '127.0.0.1:1236';
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

    /**签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSignK($Obj,$key) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String =$this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        // $this->writeLog($String);
        //签名步骤二：在string后加入KEY
        $String = $String."&accessKey=".$key;
        //echo "【string2】".$String."</br>";
        file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~加密前报文".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./sign.txt',print_r($String,true).PHP_EOL,FILE_APPEND);
        //echo $String;
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    /**字典排序 & 拼接
     * @param $paraMap
     * @param $urlencode
     * @return bool|string
     */
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    /**
     * 返回html页面代码
     */
    private function returnhtml($inputstr) {
        $json = json_encode($inputstr,true);
        $html =htmlentities($json ,ENT_QUOTES,"UTF-8");
        echo $html;
        exit();
    }

    /**获取订单表表名
     * @param $order_id
     * @return Order
     */
    private function getordertable($order_id){
        $order_sn = Redis::get('order_sn_'.$order_id);
        return Order::getordersntable($order_sn);
    }
}