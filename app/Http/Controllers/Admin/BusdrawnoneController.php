<?php
/**
created by z
 * time 2019-11-2 16:18:23
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Busdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class BusdrawnoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $busdraw=Busdraw::query();
        if(true==$request->has('business_code')){
            $busdraw->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $busdraw->whereBetween('creatime',[$start,$end]);
        }
        $data = $busdraw->where('status','=','0')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('busdrawnone.list',['list'=>$data,'input'=>$request->all()]);

    }

    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $islock=$this->buslock($id);
        if($islock){
            $res=Busdraw::pass($id);
            if($res){
                $this->unbuslock($id);
                return ['msg'=>'通过成功！','status'=>1];
            }else{
                $this->unbuslock($id);
                return ['msg'=>'通过失败！'];
            }
        }else{
            return ['msg'=>'请勿频繁操作！'];
        }

    }
    /**
     * 驳回
     */
    public function reject(StoreRequest $request){
        $id=$request->input('id');
        $key='business_lock_'.$id;
        $is=Redis::get($key);
        if(!empty($is)){
            return ['msg'=>'操作失败！'];
        }else{
            DB::beginTransaction();
            try{
                $res=Busdraw::reject($id);
                if($res){
                    //提现驳回向驳回表中插入数据-sql
                    DB::commit();
                    return ['msg'=>'驳回成功！','status'=>1];
                }else{
                    DB::rollBack();
                    return ['msg'=>'驳回失败！'];
                }
            }catch (Exception $e){
                DB::rollBack();
                return ['msg'=>'发生异常！事物进行回滚！'];
            }

        }

    }


    //redis加锁
    private function buslock($functions){

        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("business_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("business_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }
    //redis解锁
    private function unbuslock($functions){
        Redis::del("business_lock_".$functions);
    }

}