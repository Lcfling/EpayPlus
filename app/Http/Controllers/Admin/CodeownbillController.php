<?php

namespace App\Http\Controllers\Admin;

use App\Models\Billflow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeownbillController extends Controller
{
    public function own($id){
        $id=$id?$id:'';
        $map=array();
        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $map['user_id']=$id;
        $data=$account->where($map)->paginate(10);
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('codeownbill.list',['list'=>$data,'own_id'=>$id]);
    }
    public function index(Request $request){
        $map=array();
        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        $data=$account->where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('codeownbill.list',['list'=>$data,'input'=>$request->all()]);
    }
}
