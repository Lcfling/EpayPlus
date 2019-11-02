<?php
/**
created by z
 * time 2019-10-31 14:02:03
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Codeuser;
use Illuminate\Http\Request;

class CodeuserController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map=array();
        if(true==$request->has('user_id')){
            $map['user_id']=$request->input('user_id');
        }

        //导出excel
        if(true==$request->has('excel')&&($request->input('excel')=='is')){

        }

        $data = Codeuser::where($map)->paginate(10)->appends($request->all());
        return view('codeuser.list',['pager'=>$data,'input'=>$request->all()]);
    }
    /**
     * 编辑页
     */
    public function edit($user_id=0){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.edit',['id'=>$user_id,'info'=>$info]);
    }

    /**
     * 用户增加保存
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $res=$this->add_unique($data['account']);
        if(!$res){
            $pid=$data['pid']?$data['pid']:0;
            $data['mobile']=$data['account'];
            $data['pid']=intval($pid);
            $data['shenfen']=intval($data['shenfen']);
            $data['rate']=floatval($data['rate']);
            $data['rates']=floatval($data['rates']);
            $insert=Codeuser::insert($data);
            if($insert){
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }

        }else{
            return ['msg'=>'手机号已存在！'];
        }

    }

    /**
     * 保存
     */
    public function update(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        $id=$data['id'];
        unset($data['id']);
        $res=$this->edit_unique($id,$data['account']);
        if(!$res){
            $pid=$data['pid']?$data['pid']:0;
            $data['mobile']=$data['account'];
            $data['pid']=intval($pid);
            $data['shenfen']=intval($data['shenfen']);
            $data['rate']=floatval($data['rate']);
            $data['rates']=floatval($data['rates']);
            $update=Codeuser::where('user_id',$id)->update($data);
            if($update!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }else{
            return ['msg'=>'手机号已存在！'];
        }
    }

    /**
     * 删除
     */
    public function destroy($id){
        $res = Codeuser::where('user_id', $id)->delete();
        if($res){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！'];
        }
    }

    /**
     * 添加判断存在
     */
    private function add_unique($account){
        $res=Codeuser::where(array('account'=>$account))->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 编辑判断存在
     */
    private function edit_unique($id,$account){
        $res=Codeuser::where(array('account'=>$account))->whereNotIn('user_id',[$id])->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }
}