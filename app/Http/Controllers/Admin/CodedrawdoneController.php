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
        $codedraw=Codedraw::query();
        if(true==$request->has('user_id')){
            $codedraw->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $codedraw->whereBetween('creatime',[$start,$end]);
        }

        $data = $codedraw->where('status','=',1)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        return view('codedrawdone.list',['list'=>$data,'input'=>$request->all()]);

    }
}
