<?php
/**
created by z
 * time 2019-10-31 14:02:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Models\Option;
class OptionController extends Controller
{
    /**
    首页
     */
    public function index(StoreRequest $request){
        $option=Option::query();
        if(true==$request->has('key')){
            $option->where('key','like','%'.$request->input('key').'%');
        }
        if(true==$request->has('value')){
            $option->where('value','like','%'.$request->input('value').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $option->whereBetween('creatime',[$start,$end]);
        }
        $data=$option->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('options.list',['pager'=>$data,'min'=>$min,'input'=>$request->all()]);
    }


    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Option::find($id):[];
        return view('options.edit',['id'=>$id,'info'=>$info]);
    }

    /**
     * 用户增加保存
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        $res=$this->add_unique($data['key']);
        if(!$res){
            $data['creatime']=time();
            $insert=Option::insert($data);
            if($insert){
                $key=$data['key'];
                $value=$data['value'];
                Redis::set($key,$value);
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }

        }else{
            Redis::del($data['key']);
            return ['msg'=>'配置已存在！'];
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
        $info = Option::find($id);
        $key=$info['key'];
        Redis::del($key);
        $res=$this->edit_unique($id,$data['key']);
        if(!$res){
            $update=Option::where('id',$id)->update($data);
            if($update!==false){
                $k=$data['key'];
                $v=$data['value'];
                Redis::set($k,$v);
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }else{
            return ['msg'=>'配置已存在！'];
        }

    }

    /**
     * 删除
     */
    public function destroy($id){
        $info = Option::find($id);
        $key=$info['key'];
        Redis::del($key);
        $res = Option::where('id', '=', $id)->delete();
        if($res){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！'];
        }
    }

    /**
     * 添加判断存在
     */
    private function add_unique($key){
        $res=Option::where(array('key'=>$key))->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 编辑判断存在
     */
    private function edit_unique($id,$key){
        $res=Option::where(array('key'=>$key))->whereNotIn('id',[$id])->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }

}
