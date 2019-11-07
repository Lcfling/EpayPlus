<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentbill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentbillController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        $weeksuf = computeWeek(time(),false);
        $agentbill=new Agentbill();
        $agentbill->setTable('agent_billflow_'.$weeksuf);
        if(true==$request->has('agent_id')){
            $map['agent_id']=$request->input('agent_id');
        }
        $data = $agentbill->where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
        }
        return view('agentbill.list',['list'=>$data,'input'=>$request->all()]);

    }
}
