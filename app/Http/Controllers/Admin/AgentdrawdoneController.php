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
        $map=array();
        if(true==$request->has('agent_id')){
            $map['agent_id']=$request->input('agent_id');
        }
        $map['status']=1;
        $data = Agentdraw::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        return view('agentdrawdone.list',['list'=>$data,'input'=>$request->all()]);

    }
}
