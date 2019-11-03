<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Billflow;
use App\Http\Controllers\Controller;
class BillflowController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        $map=array();
        $tablepfe=date('ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);

        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        if(true==$request->has('order_id')){
            $map['order_id']=$request->input('order_id');
        }
        if(true==$request->has('erweima_id')){
            $map['erweima_id']=$request->input('erweima_id');
        }

        $data=$account->where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('billflow.list',['list'=>$data,'input'=>$request->all()]);
    }
}
