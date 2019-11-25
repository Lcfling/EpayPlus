<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentbill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentbillController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        if(true==$request->has('creattime')){
            $time = strtotime($request->input('creattime'));
            $weeksuf = computeWeek($time,false);
        }else{
            $weeksuf = computeWeek(time(),false);
        }
        $agentbill=new Agentbill();
        $agentbill->setTable('agent_billflow_'.$weeksuf);
        $sql=$agentbill->orderBy('creatime','desc');

        if(true==$request->has('order_sn')){
            $sql->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('agent_id')){
            $sql->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('business_code')){
            $sql->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('订单号','代理商ID','商户标识','积分','状态','类型','备注','创建时间');
            $excel = $sql->select('order_sn','agent_id','business_code','score','status','paycode','remark','creatime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['score']=$value['score']/100;
                $excel[$key]['status']=$this->statusName($value['status']);
                $excel[$key]['paycode']=$this->payName($value['paycode']);
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            }
            exportExcel($head,$excel,'代理商流水'.date('YmdHis',time()),'',true);
        }else{
            $data = $sql->paginate(10)->appends($request->all());
            foreach ($data as $key =>$value){
                $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            }
        }
        $min=config('admin.min_date');
        return view('agentbill.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }
    /**
     * 状态判断
     */
    protected function statusName($num){
        switch ($num){
            case $num==0:
                $name='默认';
                return $name;
                break;
            case $num==1:
                $name='支付';
                return $name;
                break;
            case $num==2:
                $name='利润';
                return $name;
                break;

        }
    }
    /**
     * paycode判断
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
