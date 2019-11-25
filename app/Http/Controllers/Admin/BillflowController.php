<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Billflow;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class BillflowController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){

        if(true==$request->has('creatime')){
            $time = strtotime($request->input('creatime'));
            $tablepfe = date('Ymd',$time);
        }else{
            $tablepfe=date('Ymd');
        }

        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $sql=$account->orderBy('creatime','desc');
        if(true==$request->has('user_id')){
            $sql->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('business_code')){
            $sql->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $sql->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('erweima_id')){
            $sql->where('erweima_id','=',$request->input('erweima_id'));
        }
        if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('码商ID','商户标识','订单号','积分','二维码ID','状态','支付类型','备注','创建时间');
            $excel = $sql->select('user_id','business_code','order_sn','score','erweima_id','status','payType','remark','creatime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['score']=$value['score']/100;
                $excel[$key]['status']=$this->statusName($value['status']);
                $excel[$key]['payType']=$this->payName($value['payType']);
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
            exportExcel($head,$excel,'码商流水'.date('YmdHis',time()),'',true);
        }else{
            $data=$sql->orderBy('creatime','desc')->paginate(10)->appends($request->all());
            foreach ($data as $key=>$value){
                $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
        }
        $min=config('admin.min_date');
        return view('billflow.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
    }
    /**
     * 状态判断
     */
    protected function statusName($num){
        switch ($num){
            case $num==1:
                $name='充值';
                return $name;
                break;
            case $num==2:
                $name='第三方支付';
                return $name;
                break;
            case $num==3:
                $name='冻结';
                return $name;
                break;
            case $num==4:
                $name='解冻';
                return $name;
                break;
            case $num==5:
                $name='佣金';
                return $name;
                break;
            case $num==6:
                $name='提现';
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
}
