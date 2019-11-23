<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Busdraw;
use App\Models\Busdrawreject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusdrawrejectController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $reject=Busdraw::query();
        if(true==$request->has('business_code')){
            $reject->where('business_code','=',$request->input('business_code'));
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
        $data = $reject->where('status','=',2)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['endtime'] =date("Y-m-d H:i:s",$value["endtime"]);
        }
        $min=config('admin.min_date');
        return view('busdrawreject.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }

    /**
     * 编辑页面
     */
    public function editreject($id){
        $info = $id?Busdrawreject::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        $bank = config('bank');
        $banklist=json_encode($bank);
        return view('busdrawreject.editinfo',['id'=>$id,'info'=>$info,'banklist'=>$banklist]);
    }
    /**
     * 保存
     */
    public function saveinfo(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $up=Busdrawreject::where('id',$id)->update($data);
        if($up){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }

    }

    /**
     * 确认驳回
     */
    public function reject(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        $pass=Busdrawreject::where('order_sn',$order_sn)->update(array('status'=>2,'endtime'=>time()));
        if($pass){
            return ['msg'=>'确认驳回成功！','status'=>1];
        }else{
            return ['msg'=>'确认驳回失败！'];
        }
    }

    /**
     * 确认打款
     */
    public function pass(StoreRequest $request){
        $order_sn=$request->input('order_sn');
        $pass1=Busdrawreject::where('order_sn',$order_sn)->update(array('status'=>1));//驳回列表
        if($pass1){
            Busdrawreject::where('order_sn',$order_sn)->update(array('endtime'=>time()));//驳回列表
            $pass2=Busdraw::where('order_sn',$order_sn)->update(array('status'=>1));//提现列表
            if($pass2){
                Busdraw::where('order_sn',$order_sn)->update(array('endtime'=>time()));//提现列表
                return ['msg'=>'确认打款成功！','status'=>1];
            }else{
                return ['msg'=>'确认打款失败！'];
            }
        }else{
            return ['msg'=>'确认打款失败！'];
        }


    }

}
