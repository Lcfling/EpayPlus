<?php

namespace App\Http\Controllers\Admin;

use App\Models\Busdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusdrawrejectController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        $map['status']=2;
        $data = Busdraw::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        return view('busdrawreject.list',['list'=>$data,'input'=>$request->all()]);

    }
}
