<?php
/**
created by z
 * time 2019-11-2 16:18:23
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Busdraw;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Tests\B;

class BusdrawnoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        $map['status']=0;
        $data = Busdraw::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('busdrawnone.list',['list'=>$data,'input'=>$request->all()]);

    }

    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $res=Busdraw::pass($id);
        if($res){
            return ['msg'=>'通过成功！','status'=>1];
        }else{
            return ['msg'=>'通过失败！'];
        }
    }
    /**
     * 驳回
     */
    public function reject(StoreRequest $request){
        $id=$request->input('id');
        $res=Busdraw::reject($id);
        if($res){
            return ['msg'=>'驳回成功！','status'=>1];
        }else{
            return ['msg'=>'驳回失败！'];
        }
    }
}