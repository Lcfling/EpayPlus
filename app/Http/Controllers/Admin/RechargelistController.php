<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Billflow;
use App\Models\Codecount;
use App\Models\Recharge;
use App\Models\Rechargelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechargelistController extends Controller
{
    /**
     * 充值审核列表
     */
    public function index(Request $request){
        $czrecord=Rechargelist::query();

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
        }
        return view('rechargelist.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
     * 通过和驳回
     */
    public function enable(StoreRequest $request){
        $id = $request->input('id');
        $status = $request->input('status');
        $info =$id?Rechargelist::find($id):[];

        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);
        $score=$info['score'];
        $user_id=$info['user_id'];
        if($status==1){
            //开启事物
            DB::beginTransaction();
            try{
                $status = Rechargelist::where('id',$request->input('id'))->update(['status'=>$status,'savetime'=>time()]);//改状态
                if(!$status){
                    DB::rollBack();
                    return ['msg'=>'审核失败！','status'=>0];
                }
                $billflow=$account->insert(['user_id'=>$user_id,'score'=>$score,'status'=>$status,'remark'=>'自动充值','creatime'=>time()]);//插数据
                if(!$billflow){
                    DB::rollBack();
                    return ['msg'=>'审核失败！','status'=>0];
                }
                $money=DB::table('users_count')->where('user_id','=',$user_id)->increment('balance',$score,['tol_sore'=>DB::raw("tol_sore + $score")]);//加钱
                if(!$money){
                    DB::rollBack();
                    return ['msg'=>'审核失败！','status'=>0];
                }
                DB::commit();
                return ['msg'=>'审核成功！','status'=>1];

            }catch (Exception $e) {
                DB::rollBack();
                return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
            }
        }else{
            $count = Rechargelist::where('id',$request->input('id'))->update(['status'=>$status,'savetime'=>time()]);
            if($count){
                return ['msg'=>'驳回成功！','status'=>1];
            }else{
                return ['msg'=>'驳回失败！','status'=>0];
            }
        }
    }
}