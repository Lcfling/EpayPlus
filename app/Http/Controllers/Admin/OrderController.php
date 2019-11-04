<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        $map=array();
        $weeksuf = computeWeek(time(),false);
        $order =new Order();
        $order->setTable('order_'.$weeksuf);

        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        if(true==$request->has('order_sn')){
            $map['order_sn']=$request->input('order_sn');
        }

        $data=$order->where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('order.list',['list'=>$data,'input'=>$request->all()]);
    }
}
