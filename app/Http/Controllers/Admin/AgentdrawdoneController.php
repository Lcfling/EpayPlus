<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentdrawdoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $agendraw=Agentdraw::query();
        if(true==$request->has('agent_id')){
            $agendraw->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('order_sn')){
            $agendraw->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $agendraw->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('endtime')){
            $creatime=$request->input('endtime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $agendraw->whereBetween('endtime',[$start,$end]);
        }
        $data = $agendraw->where('status','=',1)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        $min=config('admin.min_date');
        return view('agentdrawdone.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }
}
