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
        $busbank=Busbank::query();
        if(true==$request->has('business_code')){
            $busbank->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('name')){
            $busbank->where('name','like','%'.$request->input('name').'%');
        }
        if(true==$request->has('deposit_card')){
            $busbank->where('deposit_card','=',$request->input('deposit_card'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $busbank->whereBetween('creatime',[$start,$end]);
        }
        $data = $busbank->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('busbank.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
    }
    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Busbank::find($id):[];
        return view('busbank.edit',['id'=>$id,'info'=>$info]);
    }
}
