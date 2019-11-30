<?php
/**
created by z
 * time 2019-10-31 14:02:03
 */

namespace App\Http\Controllers\Admin;


use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Billflow;
use App\Models\Codecount;
use App\Models\Codeuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Redis;
class CodeuserController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $codeuser=Codeuser::query();

        if(true==$request->has('user_id')){
            $codeuser->where('users.user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('mobile')){
            $codeuser->where('users.mobile','=',$request->input('mobile'));
        }
        if(true==$request->has('reg_time')){
            $creatime=$request->input('reg_time');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $codeuser->whereBetween('users.reg_time',[$start,$end]);
        }
        $data = $codeuser->orderBy('reg_time','reg_time')->leftJoin('users_count','users.user_id','=','users_count.user_id')->select('users.*','users_count.balance','users_count.tol_brokerage')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['reg_time']=date("Y-m-d H:i:s",$value["reg_time"]);
        }
        $min=config('admin.min_date');
        return view('codeuser.list',['pager'=>$data,'min'=>$min,'input'=>$request->all()]);
    }
    /**
     * 编辑页
     */
    public function edit($user_id=0){
        $info = $user_id?Codeuser::find($user_id):[];
        if($user_id!=0){
            $paccount=Codeuser::where('user_id',$info['pid'])->value('account');
        }else{
            $paccount='';
        }

        return view('codeuser.edit',['id'=>$user_id,'info'=>$info,'paccount'=>$paccount]);
    }
    /**
     * 查看个人信息
     */
    public function showinfo($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        if($user_id!=0){
            $paccount=Codeuser::where('user_id',$info['pid'])->value('account');
        }else{
            $paccount='';
        }

        return view('codeuser.showinfo',['id'=>$user_id,'info'=>$info,'paccount'=>$paccount]);
    }
    /**
     * 用户增加保存
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $res=Codeuser::add_unique($data['account']);
        if($res){
            return ['msg'=>'手机号已存在！'];
        }
        DB::beginTransaction();
        try{
            $pid=$data['pid']?$data['pid']:0;
            $data['mobile']=$data['account'];
            $data['pid']=intval($pid);
            $data['password']=md5($data['password']);
            $data['shenfen']=intval($data['shenfen']);
            $data['rate']=$data['rate']/100;
            $data['rates']=$data['rates']/100;
            $data['reg_time']=time();
            $user_id=Codeuser::insertGetId($data);
            if(!$user_id){
                DB::rollBack();
                return ['msg'=>'码商添加失败！'];
            }
            $res=DB::table('users_count')->insert(array('user_id'=>$user_id,'creatime'=>time(),'savetime'=>time(),));
            if(!$res){
                DB::rollBack();
                return ['msg'=>'码商帐户添加失败！'];
            }
            DB::commit();
            return ['msg'=>'添加成功！','status'=>1];

        }catch (Exception $e){
            DB::rollBack();
            return ['msg'=>'操作异常！请稍后重试！'];
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
     * 登录封禁
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
     * 增加邀请码页面
     */
    public function addqr($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.addqr',['id'=>$user_id,'info'=>$info]);
    }
    //修改邀请码数量
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
        $info['rate']=$info['rate']*100;
        $info['rates']=$info['rates']*100;
        return view('codeuser.ownfee',['id'=>$user_id,'info'=>$info]);
    }
    //更改费率
    public function codeuserfee(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $rate=$data['rate']/100;
        $rates=$data['rates']/100;
        $res=Codeuser::where('user_id',$id)->update(array('rate'=>$rate,'rates'=>$rates));
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
    /**
     * 上分页面
     */
    public function shangfen($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.addscore',['id'=>$user_id,'info'=>$info]);
    }
    //上分
    public function codeaddscore(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $score=$data['score']*100;
        $islock=$this->codelock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            $update=DB::table('users_count')->where('user_id',$id)->update(array('savetime'=>time()));
            if(!$update){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'上分失败！','status'=>0];
            }
            $shangfen=$account->insert(['user_id'=>$id,'score'=>$score,'status'=>9,'remark'=>'手动上分','creatime'=>time()]);
            if(!$shangfen){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'上分失败！','status'=>0];
            }
            $add=DB::table('users_count')->where('user_id','=',$id)->increment('balance',$score,['shangfen'=>DB::raw("shangfen + $score")]);
            if(!$add){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'上分失败！','status'=>0];
            }
            DB::commit();
            $this->uncodelock($id);
            return ['msg'=>'上分成功！','status'=>1];

        }catch (Exception $e){
            DB::rollBack();
            $this->uncodelock($id);
            return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
        }

    }
    /**
     * 下分页面
     */
    public function xiafen($user_id){
        $info = $user_id?Codeuser::find($user_id):[];
        return view('codeuser.offscore',['id'=>$user_id,'info'=>$info]);
    }
    //下分
    public function codeoffscore(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $score=$data['score']*100;
        $islock=$this->codelock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            $update=DB::table('users_count')->where('user_id',$id)->update(array('savetime'=>time()));
            if(!$update){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'下分失败！','status'=>0];
            }
            $xiafen=$account->insert(['user_id'=>$id,'score'=>-$score,'status'=>10,'remark'=>'手动下分','creatime'=>time()]);
            if(!$xiafen){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'下分失败！','status'=>0];
            }
            $add=DB::table('users_count')->where('user_id','=',$id)->decrement('balance',$score,['xiafen'=>DB::raw("xiafen + $score")]);
            if(!$add){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'下分失败！','status'=>0];
            }
            DB::commit();
            $this->uncodelock($id);
            return ['msg'=>'下分成功！','status'=>1];

        }catch (Exception $e){
            DB::rollBack();
            $this->uncodelock($id);
            return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
        }

    }
    //redis加锁
    private function codelock($functions){

        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("codeuser_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("codeuser_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }
    //redis解锁
    private function uncodelock($functions){
        Redis::del("codeuser_lock_".$functions);
    }

}