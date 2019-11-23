<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Codedraw;
use App\Models\Codedrawreject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodedrawrejectController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $reject=Codedraw::query();
        if(true==$request->has('user_id')){
            $reject->where('user_id','=',$request->input('user_id'));
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
        return view('codedrawreject.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
    }

    /**
     * 编辑页面
     */
    public function editreject($id){
        $info = $id?Codedrawreject::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        $bank = config('bank');
        $banklist=json_encode($bank);
        return view('codedrawreject.editinfo',['id'=>$id,'info'=>$info,'banklist'=>$banklist]);
    }
    /**
     * 保存
     */
    public function saveinfo(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $up=Codedrawreject::where('id',$id)->update($data);
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
        $id=$request->input('id');
        $pass=Codedrawreject::where('id',$id)->update(array('status'=>2,'endtime'=>time()));
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
        $id=$request->input('id');
        $pass1=Codedrawreject::where('id',$id)->update(array('status'=>1));//驳回列表
        if($pass1){
            Codedrawreject::where('id',$id)->update(array('endtime'=>time()));//驳回列表
            $pass2=Codedraw::where('id',$id)->update(array('status'=>1));//提现列表
            if($pass2){
                Codedraw::where('id',$id)->update(array('endtime'=>time()));//提现列表
                return ['msg'=>'操作成功！','status'=>1];
            }else{
                return ['msg'=>'操作失败！'];
            }
        }else{
            return ['msg'=>'操作失败！'];
        }

    }
}
