<?php

namespace App\Http\Controllers\Admin;

use App\Models\Codecount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodecountController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }
        $data = Codecount::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
        }
        return view('codecount.list',['list'=>$data,'input'=>$request->all()]);

    }
}
