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
        $map=array();
        if(true==$request->has('agent_id')){
            $map['agent_id']=$request->input('agent_id');
        }
        $data = Agentbank::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('agentbank.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Agentbank::find($id):[];
        return view('agentbank.edit',['id'=>$id,'info'=>$info]);
    }
}
