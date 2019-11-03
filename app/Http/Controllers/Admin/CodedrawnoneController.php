<?php
/**
created by z
 * time 2019-11-3 10:14:52
 */
namespace App\Http\Controllers\Admin;

use App\Models\Codedraw;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;

class CodedrawnoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }
        $map['status']=0;
        $data = Codedraw::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('codedrawnone.list',['list'=>$data,'input'=>$request->all()]);

    }
    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $res=Codedraw::pass($id);
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
        $res=Codedraw::reject($id);
        if($res){
            return ['msg'=>'驳回成功！','status'=>1];
        }else{
            return ['msg'=>'驳回失败！'];
        }
    }
}
