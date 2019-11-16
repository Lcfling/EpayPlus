<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Business;
use App\Models\Order;
use App\Models\Orderrecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Billflow;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){

        $order = Orderrecord::query();

        if(true==$request->has('business_code')){
            $order->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $order->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('user_id')){
            $order->where('user_id','=',$request->input('user_id'));
        }

        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $order->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('商户ID','平台订单号','码商ID','二维码ID','码商收款','收款金额','实际到账金额','支付类型','支付状态','回调状态','创建时间');
            $excel = $order->select('business_code','order_sn','user_id','erweima_id','sk_status','sk_money','tradeMoney','payType','status','callback_status','creatime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['sk_status']=$this->sk_status($value['sk_status']);
                $excel[$key]['sk_money']=$value['sk_money']/100;
                $excel[$key]['tradeMoney']=$value['tradeMoney']/100;
                $excel[$key]['payType']=$this->payName($value['payType']);
                $excel[$key]['status']=$this->statusName($value['status']);
                $excel[$key]['callback_status']=$this->statusName($value['callback_status']);
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
            exportExcel($head,$excel,'订单记录'.date('YmdHis',time()),'',true);
        }else{
            $data=$order->paginate(10)->appends($request->all());
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
        }
        return view('order.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
     * 码商收款
     */
    protected function sk_status($type){
        switch ($type){
            case $type==0:
                $name='未收款';
                return $name;
                break;
            case $type==1:
                $name='手动收款';
                return $name;
                break;
            case $type==2:
                $name='自动收款';
                return $name;
                break;
        }
    }
    /**
     * paytype判断
     */
    protected function payName($type){
        switch ($type){
            case $type==0:
                $name='默认';
                return $name;
                break;
            case $type==1:
                $name='微信';
                return $name;
                break;
            case $type==2:
                $name='支付宝';
                return $name;
                break;
        }
    }
    /**
     * status判断
     */
    protected function statusName($type){
        switch ($type){
            case $type==0:
                $name='未支付';
                return $name;
                break;
            case $type==1:
                $name='支付成功';
                return $name;
                break;
            case $type==2:
                $name='过期';
                return $name;
                break;
            case $type==3:
                $name='取消';
                return $name;
                break;
        }
    }
    /**
     * callback判断
     */
    protected function callback($type){
        switch ($type){
            case $type==1:
                $name='推送成功';
                return $name;
                break;
            case $type==2:
                $name='推送失败';
                return $name;
                break;
        }
    }
    /**
     * @param $order_sn
     * 补单操作
     */
    public function budan(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        // 获取订单信息
        $order_info=DB::table('order_record')->where('order_sn','=',$order_sn)->first();
        $order_info=get_object_vars($order_info);
        $order =new Order();
        $order->setTable($order_info['submeter_name']);
        $order_sn_info=$order->where(array("order_sn"=>$order_info['order_sn']))->first();

        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);

        $free=$account->where(array('order_sn'=>$order_info['order_sn'],'status'=>4,'remark'=>'手动资金解冻'))->first();
        $reduce=$account->where(array('order_sn'=>$order_info['order_sn'],'status'=>2,'remark'=>'手动资金扣除'))->first();
        if(!empty($free)){
            return ['msg'=>'已手动解冻!'];
        }else if(!empty($reduce)){
            return ['msg'=>'已手动扣除!'];
        }
        $data['score']=$order_sn_info['tradeMoney'];
        $data['user_id'] = $order_sn_info['user_id'];
        $data['status']=4;
        $data['erweima_id']=$order_sn_info['erweima_id'];
        $data['business_code']=$order_sn_info['business_code'];
        $data['order_sn']=$order_sn_info['order_sn'];
        $data['remark']="手动资金解冻";
        $data['creatime']=time();

        DB::beginTransaction();
        try{
            $insert=$account->insert($data);
            if($insert){
                DB::commit();
            }else{
                DB::rollBack();
                return ['msg'=>'发生异常！'];
            }
        }catch (Exception $e){
            DB::rollBack();
            return ['msg'=>'发生异常！事物进行回滚！'];
        }

        $info['score']=-$order_sn_info['tradeMoney'];
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['order_sn']=$order_sn_info['order_sn'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        $account->insert($info);

        // 修改订单状态
        $order->where(array("order_sn"=>$order_sn_info['order_sn']))->update(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
        DB::table('order_record')->where(array("order_sn"=>$order_sn_info['order_sn']))->update(array("status"=>1,"dj_status"=>2,"pay_time"=>time()));
        $res=$this->ownpushfirst($order_sn_info['order_sn']);
        return $res;
    }

    //  超时补单
    public function csbudan(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        // 获取订单信息
        $order_info=DB::table('order_record')->where('order_sn','=',$order_sn)->first();
        $order_info=get_object_vars($order_info);
        $order =new Order();
        $order->setTable($order_info['submeter_name']);
        $order_sn_info=$order->where(array("order_sn"=>$order_info['order_sn']))->first();


        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $reduce=$account->where(array('order_sn'=>$order_info['order_sn'],'status'=>2,'remark'=>'手动资金扣除'))->first();
        if(!empty($reduce)){
            return ['msg'=>'已手动扣除!'];
        }

        $info['score']=-$order_sn_info['tradeMoney'];
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['order_sn']=$order_sn_info['order_sn'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        $account->insert($info);

        // 修改订单状态
        $order->where(array("order_sn"=>$order_sn_info['order_sn']))->update(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
        DB::table('order_record')->where(array("order_sn"=>$order_sn_info['order_sn']))->update(array("status"=>1,"dj_status"=>2,"pay_time"=>time()));
        $res=$this->ownpushfirst($order_sn_info['order_sn']);
        return $res;
    }

    public function ownpushfirst($order_sn){
        $order_info=DB::table('order_record')->where('order_sn','=',$order_sn)->first();
        $order_info=get_object_vars($order_info);
        $order =new Order();
        $order->setTable($order_info['submeter_name']);

        $orderinfo=$order->where(array("order_sn"=>$order_sn))->get();
        if($orderinfo){
            foreach ($orderinfo as $k=>$v){
                $url=$v['notifyUrl'];
                $data=array(
                    'order_sn'=>$v['order_sn'],
                    'out_order_sn'=>$v['out_order_sn'],
                    'paymoney'=>$v['payMoney'],
                    'pay_time'=>$v['pay_time'],
                    'status'=>$v['status']
                );
                $businessinfo=Business::where(array("business_code"=>$v['business_code']))->first();
                if(empty($businessinfo)){
                    return ['msg'=>'商户号不存在!回调失败!'];
                }

                $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                $res=$this->https_post_kfs($url,$data);
                file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                file_put_contents('./notifyUrl_sd.txt',$orderinfo.PHP_EOL,FILE_APPEND);
                if($res == 'success'){
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $order->where(array('id'=>$v['id']))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    //record表更新-code
                    DB::table('order_record')->where('order_sn','=',$order_sn)->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!','status'=>1];
                }else{
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    DB::table('order_record')->where(array('order_sn'=>$order_sn,'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    //record表更新-code
                    return ['msg'=>'回调成功!第三方返回失败','status'=>1];
                }
            }
        }else{
            return ['msg'=>'订单不存在!回调失败!'];
        }

    }


    /**
     *第一次 异步回调
     */
    public function sfpushfirst(StoreRequest $request){
        $order_sn=$request->input('order_sn');

        $order_info=DB::table('order_record')->where('order_sn','=',$order_sn)->first();
        $order_info=get_object_vars($order_info);
        $order =new Order();
        $order->setTable($order_info['submeter_name']);

        $orderinfo=$order->where(array("order_sn"=>$order_sn))->get();
        if($orderinfo){
            foreach ($orderinfo as $k=>$v){
                $url=$v['notifyUrl'];
                $data=array(
                    'order_sn'=>$v['order_sn'],
                    'out_order_sn'=>$v['out_order_sn'],
                    'paymoney'=>$v['payMoney'],
                    'pay_time'=>$v['pay_time'],
                    'status'=>$v['status']
                );
                $businessinfo=Business::where(array("business_code"=>$v['business_code']))->first();
                if(empty($businessinfo)){
                    return ['msg'=>'商户号不存在!回调失败!'];
                }

                $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                $res=$this->https_post_kfs($url,$data);
                file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                file_put_contents('./notifyUrl_sd.txt',$orderinfo.PHP_EOL,FILE_APPEND);
                if($res == 'success'){
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $order->where(array('id'=>$v['id']))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    DB::table('order_record')->where('order_sn','=',$order_sn)->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!','status'=>1];
                }else{
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    DB::table('order_record')->where(array('order_sn'=>$order_sn,'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!第三方返回失败','status'=>1];
                }
            }
        }else{
            return ['msg'=>'订单不存在!回调失败!'];
        }

    }

    /**签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSignK($Obj,$key){

        foreach ($Obj as $k => $v)
        {
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

        //echo $String;
        //签名步骤三：MD5加密

        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    private function https_post_kfs($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }

    /**字典排序 & 拼接
     * @param $paraMap
     * @param $urlencode
     * @return bool|string
     */
    function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }

        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

}
