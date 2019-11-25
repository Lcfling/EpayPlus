<?php


namespace App\Http\Controllers\Admin;

use App\Models\Codecount;
use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Billflow;
use App\Models\Rechargelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class RechargelistController extends Controller
{
    /**
     * 充值审核列表
     */
    public function index(Request $request){
        $czrecord=Rechargelist::query();
        $kid=Auth::id();
        $rid=getrole($kid);
        if($rid==4){
            $czrecord->where('admin_kefu_id','=',$kid);
        }
        if(true==$request->has('user_id')){
            $czrecord->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('name')){
            $czrecord->where('name','like','%'.$request->input('name').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $czrecord->whereBetween('creatime',[$start,$end]);
        }
        $data = $czrecord->where('status',0)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['czimg']='http://epp.zgzyph.com'.$value["czimg"];
        }
        $min=config('admin.min_date');
        return view('rechargelist.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);
    }
    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id = $request->input('id');

        $info =Rechargelist::find($id);

        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);

        $score=$info['score'];
        $user_id=$info['user_id'];
        $islock=$this->czlock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        //开启事物
        DB::beginTransaction();
        try{
            $status = Rechargelist::where('id',$request->input('id'))->update(['status'=>1,'savetime'=>time()]);//改状态
            if(!$status){
                DB::rollBack();
                $this->unczlock($id);
                return ['msg'=>'通过失败！','status'=>0];
            }
            $billflow=$account->insert(['user_id'=>$user_id,'score'=>$score,'status'=>1,'remark'=>'自动充值','creatime'=>time()]);//插数据
            if(!$billflow){
                DB::rollBack();
                $this->unczlock($id);
                return ['msg'=>'添加充值流水失败！','status'=>0];
            }
            $money=Codecount::where('user_id','=',$user_id)->increment('balance',$score,['tol_recharge'=>DB::raw("tol_recharge + $score")]);//加钱
            if(!$money){
                DB::rollBack();
                $this->unczlock($id);
                return ['msg'=>'更改码商帐户失败！','status'=>0];
            }
            DB::commit();
            $this->unczlock($id);
            return ['msg'=>'充值成功！','status'=>1];

        }catch (Exception $e) {
            DB::rollBack();
            $this->unczlock($id);
            return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
        }



    }

    /**
     * 驳回
     */
    public function reject(StoreRequest $request){
        $id = $request->input('id');
        $islock=$this->czlock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        $count = Rechargelist::where('id',$request->input('id'))->update(['status'=>2,'savetime'=>time()]);
        if($count){
            $this->unczlock($id);
            return ['msg'=>'驳回成功！','status'=>1];
        }else{
            $this->unczlock($id);
            return ['msg'=>'驳回失败！','status'=>0];
        }
    }


    //redis加锁
    private function czlock($functions){

        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("recharge_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("recharge_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }
    //redis解锁
    private function unczlock($functions){
        Redis::del("recharge_lock_".$functions);
    }
}