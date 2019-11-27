<?php
/**
created by z
 * time 2019-11-2 15:14:23
 */
namespace App\Http\Controllers\Admin;

use App\Models\Agent;
use App\Models\Agentbank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
class AgentController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        $agent=Agent::query();
        if(true==$request->has('id')){
            $agent->where('id','=',$request->input('id'));
        }
        if(true==$request->has('account')){
            $agent->where('account','like','%'.$request->input('account').'%');
        }
        if(true==$request->has('agent_name')){
            $agent->where('agent_name','like','%'.$request->input('agent_name').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $agent->whereBetween('creatime',[$start,$end]);
        }
        $data=$agent->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key=>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['updatetime']=date("Y-m-d H:i:s",$value["updatetime"]);
        }
        $min=config('admin.min_date');
        return view('agent.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
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
        $account=htmlformat($data['account']);
        $mobile=$data['mobile'];
        $res1=Agent::add_account($account);
        $res2=Agent::add_mobile($mobile);
        if($res1){
            return ['msg'=>'账号已存在！'];
        }
        if($res2){
            return ['msg'=>'手机号已存在！'];
        }
        DB::beginTransaction();
        try{
            $google2fa = new Google2FA();
            $secretKey=$google2fa->generateSecretKey();
            $data['ggkey']=$secretKey;
            $data['password']=bcrypt($data['password']);
            $data['creatime']=time();
            $data['updatetime']=time();
            $agent_id=Agent::insertGetId($data);
            if(!$agent_id){
                DB::rollBack();
                return ['msg'=>'代理添加失败！'];
            }
            $res=DB::table('agent_count')->insert(array('agent_id'=>$agent_id,'creatime'=>time(),'savetime'=>time()));
            if(!$res){
                DB::rollBack();
                return ['msg'=>'代理帐户添加失败！'];
            }
            DB::commit();
            return ['msg'=>'添加成功！','status'=>1];
        }catch (Exception $e){
            DB::rollBack();
            return ['msg'=>'操作异常！请稍后重试！'];
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
     * 银行信息页
     */
    public function agentbankinfo($id){
        $info = $id?Agent::find($id):[];
        $bank=Agentbank::where('agent_id','=',$id)->get()->toArray();
        return view('agent.bankinfo',['id'=>$id,'info'=>$info,'bank'=>$bank]);
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
    /**
     * 修改登录密码页
     */
    public function editpayword($id){
        $info = $id?Agent::find($id):[];
        return view('agent.editpayword',['id'=>$id,'info'=>$info]);
    }
    /**
     * 修改登录密码
     */
    public function changepayword(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $payword=md5(md5($data['pay_pass']));
        $res=Agent::where('id',$id)->update(array('pay_pass'=>$payword,'updatetime'=>time()));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }
    /**
     * 开关
     */
    public function agent_switch(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $aswitch=$data['aswitch'];
        $res=Agent::where('id',$id)->update(array('status'=>$aswitch,'updatetime'=>time()));
        if($res){
            return ['msg'=>'更改成功！','status'=>1];
        }else{
            return ['msg'=>'更改失败！'];
        }
    }
    /**
     * 登录
     */
    public function agent_islogin(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $login=$data['login'];
        $res=Agent::where('id',$id)->update(array('is_login'=>$login,'updatetime'=>time()));
        if($res){
            return ['msg'=>'更改成功！','status'=>1];
        }else{
            return ['msg'=>'更改失败！'];
        }
    }
}
