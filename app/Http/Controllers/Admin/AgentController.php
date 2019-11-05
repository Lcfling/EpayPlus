<?php
/**
created by z
 * time 2019-11-2 15:14:23
 */
namespace App\Http\Controllers\Admin;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
class AgentController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        $map=array();
        if(true==$request->has('id')){
            $map['id']=$request->input('id');
        }
        $data=Agent::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['updatetime']=date("Y-m-d H:i:s",$value["updatetime"]);
        }
        return view('agent.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
     * 添加/编辑页
     */
    public function edit($id=0){
        $info = $id?Agent::find($id):[];
        return view('agent.edit',['id'=>$id,'info'=>$info]);
    }
    /**
     * 添加数据
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $account=$data['account'];
        $mobile=$data['mobile'];
        $res1=Agent::add_account($account);
        $res2=Agent::add_mobile($mobile);
        if($res1){
            return ['msg'=>'账号已存在！'];
        }else if($res2){
            return ['msg'=>'手机号已存在！'];
        }else{
            $data['password']=bcrypt($data['password']);
            $data['creatime']=time();
            $res=Agent::insert($data);
            if($res){
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }
        }

    }
    /**
     * 修改数据
     */
    public function update(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $account=$data['account'];
        $mobile=$data['mobile'];
        $res1=Agent::edit_account($id,$account);
        $res2=Agent::edit_mobile($id,$mobile);
        if($res1){
            return ['msg'=>'账号已存在！'];
        }else if($res2){
            return ['msg'=>'手机号已存在！'];
        }else{
            $data['updatetime']=time();
            $res=Agent::where('id',$id)->update($data);
            if($res!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }

    }
    /**
     * 修改登录密码页
     */
    public function editpwd($id){
        $info = $id?Agent::find($id):[];
        return view('agent.editpwd',['id'=>$id,'info'=>$info]);
    }
    /**
     * 修改登录密码
     */
    public function changepwd(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $pwd=bcrypt($data['password']);
        $res=Agent::where('id',$id)->update(array('password'=>$pwd,'updatetime'=>time()));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }

}
