<?php

namespace App\Http\Controllers\Admin;

use App\Models\Codedraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodedrawdoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }
        $map['status']=1;
        $data = Codedraw::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['withdraw_time'] =date("Y-m-d H:i:s",$value["withdraw_time"]);
        }
        return view('codedrawdone.list',['list'=>$data,'input'=>$request->all()]);

    }
}
