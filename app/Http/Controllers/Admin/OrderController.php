<?php
/**
 * 订单过期处理
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Buscount;
use App\Models\Qrcode;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\Business;
use App\Models\Codecount;
use App\Models\Rebate;
class OrderController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){

        if(true==$request->has('creatime')){
            $time = strtotime($request->input('creatime'));
            $weeksuf = computeWeek($time,false);
        }else{
            $weeksuf = computeWeek(time(),false);
        }

        $order=new Order;
        $order->setTable('order_'.$weeksuf);
        $sql=$order->orderBy('creatime','desc');

        if(true==$request->has('business_code')){
            $sql->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $sql->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('out_order_sn')){
            $sql->where('out_order_sn','=',$request->input('out_order_sn'));
        }
        if(true==$request->has('user_id')){
            $sql->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('status')){
            $sql->where('status','=',$request->input('status'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $sql->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('商户标识','平台订单号','商户订单号','码商ID','二维码ID','码商收款','收款金额','实际到账金额','支付类型','支付状态','回调状态','创建时间');
            $excel = $sql->where([["status",2],['user_id','>',0]])->where('status',2)->select('business_code','order_sn','out_order_sn','user_id','erweima_id','sk_status','sk_money','tradeMoney','payType','status','callback_status','creatime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['sk_status']=$this->sk_status($value['sk_status']);
                $excel[$key]['sk_money']=$value['sk_money']/100;
                $excel[$key]['tradeMoney']=$value['tradeMoney']/100;
                $excel[$key]['payType']=$this->payName($value['payType']);
                $excel[$key]['status']='过期';
                $excel[$key]['callback_status']=$this->callback($value['callback_status']);
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
            exportExcel($head,$excel,'订单过期记录'.date('YmdHis',time()),'',true);
        }else{
            $data=$sql->where([["status",2],['user_id','>',0]])->paginate(10)->appends($request->all());
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
        }
        $min=config('admin.min_date');
        return view('order.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
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
    /**过期订单->补单
     * @param $order_sn
     *
     */
    public function budan(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        // 获取订单信息

        $order =Order::getordersntable($order_sn);
        $account =Order::getcounttable($order_sn);

        $islock=Order::orderlock($order_sn);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }

        DB::beginTransaction();
        try{
            if(!$order_info=$order->where([["order_sn",$order_sn],["status",2]])->lockForUpdate()->first()){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'订单已处理！'];
            }
            $data=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>4,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金解冻",
                'creatime'=>time(),
            ];
            //插入解冻流水
            $insert=$account->insert($data);
            if(!$insert){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'解冻失败！'];
            }
            $info=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>2,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金扣除",
                'creatime'=>time(),
            ];
            //插入扣除流水
            $account->insert($info);
            if(!$account){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'扣除失败！'];
            }
            $rate=[
                'user_id'=>$order_info['user_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'tradeMoney'=>$order_info['tradeMoney'],
                'payType'=>$order_info['payType'],
                'creatime'=>time(),
            ];
            //插入返佣表
            $rebate=Rebate::insert($rate);
            if(!$rebate){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'返佣失败！'];
            }
            //码商帐户减钱
            $tradeMoney=$order_info['tradeMoney'];
            $fremoney=Codecount::where('user_id',$order_info['user_id'])->decrement('freeze_money',$tradeMoney,['tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
            if(!$fremoney){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'修改码商帐户失败！'];
            }
            //更改订单状态
            $djstatus=$order->where(array("order_sn"=>$order_info['order_sn']))->update(array("status"=>1,'sk_money'=>$tradeMoney,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
            if(!$djstatus){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改订单状态失败！'];
            }
            $qrcode_score=Qrcode::where(array('id'=>$order_info['erweima_id'],'user_id'=>$order_info['user_id']))->increment('sumscore',$tradeMoney);
            if(!$qrcode_score){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改二维码跑分失败！'];
            }
            $done_num=Buscount::where(array('business_code'=>$order_info['business_code']))->increment('done_num',1);
            if(!$done_num){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'增加商户成功订单失败！'];
            }
            DB::commit();
            Order::unorderlock($order_sn);
            return $this->ownpushfirst($order_info['order_sn']);

        }catch (Exception $e){
            DB::rollBack();
            Order::unorderlock($order_sn);
            return ['msg'=>'发生异常！事物进行回滚！'];
        }

    }

    //  取消订单->超时补单
    public function csbudan(StoreRequest $request){
        $order_sn=$request->input('order_sn');

        // 获取订单信息
        $order =Order::getordersntable($order_sn);
        $account =Order::getcounttable($order_sn);

        $islock=Order::orderlock($order_sn);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }


        DB::beginTransaction();
        try{
            if(!$order_info=$order->where([["order_sn",$order_sn],['status',3]])->lockForUpdate()->first()){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'订单已处理！'];
            }
            $info=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>2,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金扣除",
                'creatime'=>time(),
            ];
            //扣除-流水
            $account->insert($info);
            if(!$account){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'扣除失败！'];
            }
            $rate=[
                'user_id'=>$order_info['user_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'tradeMoney'=>$order_info['tradeMoney'],
                'payType'=>$order_info['payType'],
                'creatime'=>time(),
            ];
            //返佣
            $rebate=Rebate::insert($rate);
            if(!$rebate){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'返佣失败！'];
            }
            //帐户修改
            $tradeMoney=$order_info['tradeMoney'];
            $fremoney=Codecount::where('user_id',$order_info['user_id'])->decrement('balance',$tradeMoney,['tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
            if(!$fremoney){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'修改码商帐户失败！'];
            }
            //订单状态
            $djstatus=$order->where(array("order_sn"=>$order_info['order_sn']))->update(array("status"=>1,'sk_money'=>$tradeMoney,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
            if(!$djstatus){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改订单状态失败！'];
            }
            $qrcode_score=Qrcode::where(array('id'=>$order_info['erweima_id'],'user_id'=>$order_info['user_id']))->increment('sumscore',$tradeMoney);
            if(!$qrcode_score){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改二维码跑分失败！'];
            }
            $done_num=Buscount::where(array('business_code'=>$order_info['business_code']))->increment('done_num',1);
            if(!$done_num){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'增加商户成功订单失败！'];
            }
            DB::commit();
            Order::unorderlock($order_sn);
            return $this->ownpushfirst($order_info['order_sn']);

        }catch (Exception $e){
            DB::rollBack();
            Order::unorderlock($order_sn);
            return ['msg'=>'发生异常！事物进行回滚！'];
        }

    }

    /**异常订单补单
     * @param StoreRequest $request
     * @return array
     */
    public function falsebudan(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        // 获取订单信息

        $order =Order::getordersntable($order_sn);
        $account =Order::getcounttable($order_sn);

        $islock=Order::orderlock($order_sn);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }

        DB::beginTransaction();
        try{
            if(!$order_info=$order->where([["order_sn",$order_sn],['status',4]])->lockForUpdate()->first()){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'订单已处理！'];
            }
            $data=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>4,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金解冻",
                'creatime'=>time(),
            ];
            //插入解冻流水
            $insert=$account->insert($data);
            if(!$insert){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'解冻失败！'];
            }
            $info=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>2,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金扣除",
                'creatime'=>time(),
            ];
            //插入扣除流水
            $account->insert($info);
            if(!$account){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'扣除失败！'];
            }
            $rate=[
                'user_id'=>$order_info['user_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'tradeMoney'=>$order_info['tradeMoney'],
                'payType'=>$order_info['payType'],
                'creatime'=>time(),
            ];
            //插入返佣表
            $rebate=Rebate::insert($rate);
            if(!$rebate){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'返佣失败！'];
            }
            //码商帐户减钱
            $tradeMoney=$order_info['tradeMoney'];
            $fremoney=Codecount::where('user_id',$order_info['user_id'])->decrement('freeze_money',$tradeMoney,['tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
            if(!$fremoney){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'修改码商帐户失败！'];
            }
            //更改订单状态
            $djstatus=$order->where(array("order_sn"=>$order_info['order_sn']))->update(array("status"=>1,'sk_money'=>$tradeMoney,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
            if(!$djstatus){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改订单状态失败！'];
            }
            $qrcode_score=Qrcode::where(array('id'=>$order_info['erweima_id'],'user_id'=>$order_info['user_id']))->increment('sumscore',$tradeMoney);
            if(!$qrcode_score){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改二维码跑分失败！'];
            }
            $done_num=Buscount::where(array('business_code'=>$order_info['business_code']))->increment('done_num',1);
            if(!$done_num){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'增加商户成功订单失败！'];
            }
            DB::commit();
            Order::unorderlock($order_sn);
            return $this->ownpushfirst($order_info['order_sn']);

        }catch (Exception $e){
            DB::rollBack();
            Order::unorderlock($order_sn);
            return ['msg'=>'发生异常！事物进行回滚！'];
        }

    }

    /**异常订单手动解冻
     * @param StoreRequest $request
     * @return array
     */
    public function jiedong(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        // 获取订单信息

        $order =Order::getordersntable($order_sn);
        $account =Order::getcounttable($order_sn);

        $islock=Order::orderlock($order_sn);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }

        DB::beginTransaction();
        try{
            if(!$order_info=$order->where([["order_sn",$order_sn],['status',4],['dj_status',0]])->lockForUpdate()->first()){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'订单已处理！'];
            }
            $data=[
                'score'=>$order_info['tradeMoney'],
                'user_id'=>$order_info['user_id'],
                'status'=>4,
                'erweima_id'=>$order_info['erweima_id'],
                'business_code'=>$order_info['business_code'],
                'order_sn'=>$order_info['order_sn'],
                'remark'=>"手动资金解冻",
                'creatime'=>time(),
            ];
            //插入解冻流水
            $insert=$account->insert($data);
            if(!$insert){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'解冻失败！'];
            }
            //码商帐户减钱
            $tradeMoney=$order_info['tradeMoney'];
            $fremoney=Codecount::where('user_id',$order_info['user_id'])->decrement('freeze_money',$tradeMoney,['balance'=>DB::raw("balance + $tradeMoney")]);
            if(!$fremoney){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'修改码商帐户失败！'];
            }
            //订单状态
            $djstatus=$order->where(array("order_sn"=>$order_info['order_sn']))->update(array("status"=>3,"dj_status"=>1));
            if(!$djstatus){
                DB::rollBack();
                Order::unorderlock($order_sn);
                return ['msg'=>'更改订单状态失败！'];
            }
            DB::commit();
            Order::unorderlock($order_sn);
            return ['msg'=>'解冻成功！','status'=>1];
        }catch (Exception $e){
            DB::rollBack();
            Order::unorderlock($order_sn);
            return ['msg'=>'发生异常！事物进行回滚！'];
        }
    }

    /**
     * 手动回调
     */
    private function ownpushfirst($order_sn) {
        $ordertable =Order::getordersntable($order_sn);
        $orderinfo=$ordertable->where(array("order_sn"=>$order_sn))->get();
        if($orderinfo) {
            foreach ($orderinfo as $k=>$v) {
                $url=$v['notifyUrl'];
                $data=array(
                    'order_sn'=>$v['order_sn'],
                    'out_order_sn'=>$v['out_order_sn'],
                    'sk_money'=>$v['sk_money'],
                    'pay_time'=>$v['pay_time'],
                    'status'=>$v['status']
                );
                $businessinfo=Business::where(array("business_code"=>$v['business_code']))->first();
                if(empty($businessinfo)) {
                    return ['msg'=>'商户号不存在!回调失败!'];
                }
                $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                $res=$this->https_post_kfs($url,$data);
                file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                file_put_contents('./notifyUrl_sd.txt',$orderinfo.PHP_EOL,FILE_APPEND);
                if($res == 'success') {
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $ordertable->where(array('id'=>$v['id']))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!','status'=>1];
                } else {
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $ordertable->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!第三方返回失败'];
                }
            }
        } else {
            return ['msg'=>'订单不存在!回调失败!'];
        }
    }



    /**
     *第一次 异步回调
     */
    public function sfpushfirst(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        $ordertable =Order::getordersntable($order_sn);
        $orderinfo=$ordertable->where(array("order_sn"=>$order_sn))->get();
        if($orderinfo) {
            foreach ($orderinfo as $k=>$v) {
                $url=$v['notifyUrl'];
                $data=array(
                    'order_sn'=>$v['order_sn'],
                    'out_order_sn'=>$v['out_order_sn'],
                    'sk_money'=>$v['sk_money'],
                    'pay_time'=>$v['pay_time'],
                    'status'=>$v['status']
                );
                $businessinfo=Business::where(array("business_code"=>$v['business_code']))->first();
                if(empty($businessinfo)) {
                    return ['msg'=>'商户号不存在!回调失败!'];
                }
                $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                $res=$this->https_post_kfs($url,$data);
                file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                file_put_contents('./notifyUrl_sd.txt',$orderinfo.PHP_EOL,FILE_APPEND);
                if($res == 'success') {
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $ordertable->where(array('id'=>$v['id']))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!','status'=>1];
                } else {
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                    $ordertable->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    return ['msg'=>'回调成功!第三方返回失败','status'=>1];
                }
            }
        } else {
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
