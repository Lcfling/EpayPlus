<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Agentdraw;
use App\Models\Agentdrawreject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentdrawrejectController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $reject=Agentdraw::query();
        if(true==$request->has('agent_id')){
            $reject->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('order_sn')){
            $reject->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $reject->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('endtime')){
            $savetime=$request->input('endtime');
            $start=strtotime($savetime);
            $end=strtotime('+1day',$start);
            $reject->whereBetween('endtime',[$start,$end]);
        }

        $data = $reject->where('status',2)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        $min=config('admin.min_date');
        return view('agentdrawreject.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }

    /**
     * 编辑页面
     */
    public function editreject($id){
        $info = $id?Agentdrawreject::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        $bank = config('bank');
        $banklist=json_encode($bank);
        return view('agentdrawreject.editinfo',['id'=>$id,'info'=>$info,'banklist'=>$banklist]);
    }
    /**
     * 保存
     */
    public function saveinfo(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $up=Agentdrawreject::where('id',$id)->update($data);
        if($up){
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！'];
        }

    }
    /**
     * 确认驳回
     */
    public function reject(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        $pass=Agentdrawreject::where('order_sn',$order_sn)->update(array('status'=>2,'endtime'=>time()));
        if($pass){
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！'];
        }
    }
    /**
     * 确认打款
     */
    public function pass(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        $pass1=Agentdrawreject::where('order_sn',$order_sn)->update(array('status'=>1));//驳回列表
        if($pass1){
            Agentdrawreject::where('order_sn',$order_sn)->update(array('endtime'=>time()));//驳回列表
            $pass2=Agentdraw::where('order_sn',$order_sn)->update(array('status'=>1));//提现列表
            if($pass2){
                Agentdraw::where('order_sn',$order_sn)->update(array('endtime'=>time()));//提现列表
                return ['msg'=>'操作成功！','status'=>1];
            }else{
                return ['msg'=>'操作失败！'];
            }
        }else{
            return ['msg'=>'操作失败！'];
        }

    }


}
