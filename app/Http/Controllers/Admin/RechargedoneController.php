<?php

namespace App\Http\Controllers\Admin;

use App\Models\Rechargelist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RechargedoneController extends Controller
{
    /**
     * 充值通过列表
     */
    public function index(Request $request){
        $czrecord=Rechargelist::query();

        if(true==$request->has('user_id')){
            $czrecord->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('name')){
            $czrecord->where('name','like','%'.$request->input('name').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $czrecord->whereBetween('creatime',[$start,$end]);
        }
        $data = $czrecord->where('status',1)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
        }
        return view('rechargedone.list',['list'=>$data,'input'=>$request->all()]);
    }
}
