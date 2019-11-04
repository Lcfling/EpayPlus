<?php

namespace App\Http\Controllers\Admin;

use App\Models\Buscount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuscountController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('business_id')){
            $map['business_id']=$request->input('business_id');
        }
        $data = Buscount::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
        }
        return view('buscount.list',['list'=>$data,'input'=>$request->all()]);

    }
}
