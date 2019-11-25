<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderundoController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        //$this->test_table_data();
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
            $excel = $sql->where([["status",3],['user_id','>',0]])->select('business_code','order_sn','out_order_sn','user_id','erweima_id','sk_status','sk_money','tradeMoney','payType','status','callback_status','creatime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['sk_status']=$this->sk_status($value['sk_status']);
                $excel[$key]['sk_money']=$value['sk_money']/100;
                $excel[$key]['tradeMoney']=$value['tradeMoney']/100;
                $excel[$key]['payType']=$this->payName($value['payType']);
                $excel[$key]['status']='取消';
                $excel[$key]['callback_status']=$this->callback($value['callback_status']);
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
            exportExcel($head,$excel,'订单取消记录'.date('YmdHis',time()),'',true);
        }else{
            $data=$sql->where([["status",3],['user_id','>',0]])->paginate(10)->appends($request->all());
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
        }
        $min=config('admin.min_date');
        return view('orderundo.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
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
}
