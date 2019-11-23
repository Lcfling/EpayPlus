<?php

namespace App\Http\Controllers\Admin;

use App\Models\Busdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusdrawdoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $busdraw=Busdraw::query();
        if(true==$request->has('business_code')){
            $busdraw->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('order_sn')){
            $busdraw->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $busdraw->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('endtime')){
            $creatime=$request->input('endtime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $busdraw->whereBetween('endtime',[$start,$end]);
        }
        $data = $busdraw->where('status','=',1)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        $min=config('admin.min_date');
        return view('busdrawdone.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }
}
