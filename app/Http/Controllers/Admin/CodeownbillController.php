<?php

namespace App\Http\Controllers\Admin;

use App\Models\Billflow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeownbillController extends Controller
{
    public function own($id,Request $request){
        $id=$id?$id:'';

        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $sql=$account->orderBy('creatime','desc');

        if(true==$request->has('business_code')){
            $sql->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $sql->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('erweima_id')){
            $sql->where('erweima_id','=',$request->input('erweima_id'));
        }
        $data=$sql->where('user_id',$id)->paginate(10)->appends($request->all());;
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('codeownbill.list',['list'=>$data,'own_id'=>$id,'min'=>$min,'input'=>$request->all()]);
    }

    public function index(Request $request){
        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $sql=$account->orderBy('creatime','desc');
        if(true==$request->has('business_code')){
            $sql->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $sql->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('erweima_id')){
            $sql->where('erweima_id','=',$request->input('erweima_id'));
        }
        $data=$sql->where('user_id',$request->input('user_id'))->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('codeownbill.list',['list'=>$data,'input'=>$request->all()]);
    }
}
