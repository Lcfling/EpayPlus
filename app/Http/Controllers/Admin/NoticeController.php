<?php
/**
created by z
 * time 2019-11-01 11:25:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Notice;
class NoticeController extends Controller
{
    /**
     * 公告列表
     */
    public function index()
    {
        $data=Notice::paginate();
        foreach ($data as $key =>$value){
            $data[$key]['creattime']=date("Y-m-d H:i:s",$value["creattime"]);
        }
        return view('notices.list',['pager'=>$data]);
    }

    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Notice::find($id):[];
        return view('notices.edit',['id'=>$id,'info'=>$info]);
    }

    /**
     * 用户增加保存
     */
    public function store(StoreRequest $request){
        $id =$request->input('id');
        $data=$request->all();
        unset($data['_token']);
        $data['creattime']=time();
        $insert=Notice::insert($data);
        if($insert){
            return ['msg'=>'添加成功！','status'=>1];
        }else{
            return ['msg'=>'添加失败！'];
        }

    }

    /**
     * 保存
     */
    public function update(StoreRequest $request){
        $id =$request->input('id');
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);

        $update=Notice::where('id',$id)->update($data);
            if($update!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }

    }
}
