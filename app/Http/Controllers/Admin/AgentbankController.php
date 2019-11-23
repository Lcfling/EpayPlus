<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentbank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentbankController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $agentbank=Agentbank::query();

        if(true==$request->has('agent_id')){
            $agentbank->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('name')){
            $agentbank->where('name','like','%'.$request->input('name').'%');
        }
        if(true==$request->has('deposit_card')){
            $agentbank->where('deposit_card','=',$request->input('deposit_card'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $agentbank->whereBetween('creatime',[$start,$end]);
        }
        $data = $agentbank->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('agentbank.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
    }
    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Agentbank::find($id):[];
        return view('agentbank.edit',['id'=>$id,'info'=>$info]);
    }
}
