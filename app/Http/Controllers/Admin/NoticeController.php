<?php
/**
created by z
 * time 2019-11-01 11:25:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use PragmaRX\Google2FA\Google2FA;

class NoticeController extends Controller
{
    /**
     * 公告列表
     */
    public function index(StoreRequest $request)
    {
        $notice=Notice::query();
        if(true==$request->has('title')){
            $notice->where('title','like','%'.$request->input('title').'%');
        }
        if(true==$request->has('content')){
            $notice->where('content','like','%'.$request->input('content').'%');
        }
        if(true==$request->has('creattime')){
            $creatime=$request->input('creattime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $notice->whereBetween('creattime',[$start,$end]);
        }
        $data=$notice->orderBy('creattime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creattime']=date("Y-m-d H:i:s",$value["creattime"]);
        }
        $min=config('admin.min_date');
        return view('notices.list',['pager'=>$data,'min'=>$min,'input'=>$request->all()]);
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
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
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
    /**
     * 删除
     */
    public function destroy($id){
        $res = Notice::where('id',$id)->delete();
        if($res){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！'];
        }
    }
}
