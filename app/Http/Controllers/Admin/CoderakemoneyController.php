<?php
/**
created by z
 * time 2019-11-01 16:40:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Coderakemoney;
class CoderakemoneyController extends Controller
{
    /**
     * 佣金配置
     */
    public function index(){
        $data=Coderakemoney::get()->toArray();
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('coderakemoney.list',['list'=>$data]);
    }

    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Coderakemoney::find($id):[];
        return view('coderakemoney.edit',['id'=>$id,'info'=>$info]);
    }
    /**
     * 保存
     */
    public function update(StoreRequest $request){
        $id =$request->input('id');
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $update=Coderakemoney::where('id',$id)->update($data);
        if($update!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }


    }
}
