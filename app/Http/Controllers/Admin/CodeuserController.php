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
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;

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
        $res=Codeuser::add_unique($data['account']);
        if(!$res){
            $google2fa = new Google2FA();
            $secretKey=$google2fa->generateSecretKey();
            $data['ggkey']=$secretKey;
            $pid=$data['pid']?$data['pid']:0;
            $data['mobile']=$data['account'];
            $data['pid']=intval($pid);
            $data['password']=md5($data['password']);
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
        $res=Codeuser::edit_unique($id,$data['account']);
        if(!$res){
            $pid=$data['pid']?$data['pid']:0;
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
     * 登录
     */
    public function codeuser_isover(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $is_over=$data['is_over'];
        $res=Codeuser::where('user_id',$id)->update(array('is_over'=>$is_over));
        if($res){
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！'];
        }
    }
    /**
     * 增加二维码页面
     */
    public function addqr($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.addqr',['id'=>$user_id,'info'=>$info]);
    }
    //修改二维码数量
    public function codeaddqr(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $res=Codeuser::where('user_id',$id)->update(array('imsi_num'=>intval($data['imsi_num'])));
        if($res!==false){
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！'];
        }

    }
    //通知页面
    public function tomsg($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.tomsg',['id'=>$user_id,'info'=>$info]);
    }
    //添加通知
    public function codeputmsg(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $msg=[
            'ifread'=>0,
            'title'=>$data['title'],
            'content'=>$data['content'],
            'creatime'=>time(),
            'user_id'=>$id,
            'remark'=>'消息通知',
        ];
        $insert=DB::table('message')->insert($msg);
        if($insert){
            return ['msg'=>'添加成功！','status'=>1];
        }else{
            return ['msg'=>'添加失败！'];
        }
    }
    //费率页面
    public function ownfee($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.ownfee',['id'=>$user_id,'info'=>$info]);
    }
    //更改费率
    public function codeuserfee(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $res=Codeuser::where('user_id',$id)->update(array('rate'=>floatval($data['rate']),'rates'=>floatval($data['rates'])));
        if($res!==false){
            return ['msg'=>'操作成功！','status'=>1];
        }else{
            return ['msg'=>'操作失败！'];
        }
    }
    //登录密码页面
    public function logpwd($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.logpwd',['id'=>$user_id,'info'=>$info]);
    }
    //修改登录密码
    public function codenewpwd(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $pwd=md5($data['password']);
        $res=Codeuser::where('user_id',$id)->update(array('password'=>$pwd));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }

    }
    //二级密码
    public function secondpwd($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.secondpwd',['id'=>$user_id,'info'=>$info]);
    }
    //修改二级密码
    public function codenewTwopwd(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $pwd=md5($data['second_pwd']);
        $res=Codeuser::where('user_id',$id)->update(array('second_pwd'=>$pwd));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }
    //支付密码
    public function zfpwd($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.zfpwd',['id'=>$user_id,'info'=>$info]);
    }
    //修改支付密码
    public function codenewpaypwd(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $pwd=md5($data['zf_pwd']);
        $res=Codeuser::where('user_id',$id)->update(array('zf_pwd'=>$pwd));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }
}