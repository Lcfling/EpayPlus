<?php

namespace App\Http\Controllers\Admin;

use App\Models\Busbank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusbankController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        $data = Busbank::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('busbank.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Busbank::find($id):[];
        return view('busbank.edit',['id'=>$id,'info'=>$info]);
    }
}
