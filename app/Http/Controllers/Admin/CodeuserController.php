<?php


namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Codeuser;

class CodeuserController extends Controller
{
    /**
     * 数据列表
     */
    public function index(){
        $data = Codeuser::paginate();
        return view('codeuser.list',['pager'=>$data]);
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
            $data['account']=intval($data['account']);
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