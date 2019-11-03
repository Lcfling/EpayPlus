<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Recharge;
use App\Models\Rechargelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechargelistController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $map = array();
        $data = Rechargelist::where($map)->paginate(10)->appends($request->all());
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
        if($status==1){
            //开启事物
            DB::beginTransaction();
            try{
                DB::table('account_log')->insert(['user_id'=>$info['user_id'],'score'=>$info['score'],'status'=>$status,'remark'=>'自动充值','creatime'=>time()]);
                $count = Rechargelist::where('id',$request->input('id'))->update(['status'=>$status]);
                if($count){
                    DB::commit();
                    return ['msg'=>'审核成功！','status'=>1];
                }else{
                    DB::rollBack();
                    return ['msg'=>'审核失败！','status'=>0];
                }
            }catch (Exception $e) {
                DB::rollBack();
                return ['msg'=>'发生异常！事物进行回滚！','status'=>0];
            }
        }else{
            $count = Rechargelist::where('id',$request->input('id'))->update(['status'=>$status]);
            if($count){
                return ['msg'=>'驳回成功！','status'=>1];
            }else{
                return ['msg'=>'驳回失败！','status'=>0];
            }
        }
    }
}