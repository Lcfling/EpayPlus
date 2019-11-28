<?php
/**
created by z
 * time 2019-11-2 16:18:23
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Busbill;
use App\Models\Buscount;
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
        if(true==$request->has('order_sn')){
            $busdraw->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $busdraw->whereBetween('creatime',[$start,$end]);
        }
        $data = $busdraw->where('status','=',0)->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('busdrawnone.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }

    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');

        $drawinfo=Busdraw::find($id);

        $islock=$this->buslock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }

        DB::beginTransaction();
        try{
            if(!$draw=Busdraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
                DB::rollBack();
                $this->unbuslock($id);
                return ['msg'=>'订单已处理！'];
            }else{
                $status=Busdraw::pass($id);
                if(!$status){
                    DB::rollBack();
                    $this->unbuslock($id);
                    return ['msg'=>'通过失败！'];
                }else{
                    $drawMoney=$drawinfo['money'];
                    $tradeMoney=$drawinfo['tradeMoney'];
                    $add=Buscount::where('business_code',$drawinfo['business_code'])->increment('drawMoney',$drawMoney,['tradeMoney'=>DB::raw("tradeMoney + $tradeMoney")]);
                    if(!$add){
                        DB::rollBack();
                        $this->unbuslock($id);
                        return ['msg'=>'更改商户帐户失败！'];
                    }else{
                        DB::commit();
                        $this->unbuslock($id);
                        return ['msg'=>'通过成功！','status'=>1];
                    }
                }
            }
        }catch (Exception $e){
            DB::rollBack();
            $this->unbuslock($id);
            return ['msg'=>'操作异常！请稍后重试！'];
        }

    }

    /**
     * 驳回页面
     */
    public function bohui($id){
        $info = $id?Busdraw::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        return view('busdrawnone.bohui',['id'=>$id,'info'=>$info]);
    }


    /**
     * 驳回
     */
    public function reject(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        $drawinfo=Busdraw::find($id);

        $weeksuf = computeWeek(time(),false);
        $busbill=new Busbill();
        $busbill->setTable('business_billflow_'.$weeksuf);
        $islock=$this->buslock($id);
        if(!$islock){
          return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            if(!$draw=Busdraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
                DB::rollBack();
                $this->unbuslock($id);
                return ['msg'=>'订单已处理！'];
            }else{
                //改状态 插流水 减帐户钱
                $status=Busdraw::reject($id,$data['remark']);
                if(!$status){
                    DB::rollBack();
                    $this->unbuslock($id);
                    return ['msg'=>'驳回失败！'];
                }else{
                    $bill=[
                        'order_sn'=>$drawinfo['order_sn'],
                        'business_code'=>$drawinfo['business_code'],
                        'tradeMoney'=>$drawinfo['money'],
                        'score'=>$drawinfo['tradeMoney'],
                        'status'=>3,
                        'remark'=>'商户提现驳回',
                        'creatime'=>time()
                    ];
                    $ins=$busbill->insert($bill);
                    if(!$ins){
                        DB::rollBack();
                        $this->unbuslock($id);
                        return ['msg'=>'商户流水添加失败！'];
                    }else{
                        $drawMoney=$drawinfo['money'];
                        $reduce=Buscount::where('business_code',$drawinfo['business_code'])->increment('balance',$drawMoney);
                        if(!$reduce){
                            DB::rollBack();
                            $this->unbuslock($id);
                            return ['msg'=>'更改商户帐户失败！'];
                        }else{
                            DB::commit();
                            $this->unbuslock($id);
                            return ['msg'=>'驳回成功！','status'=>1];
                        }
                    }
                }
            }
        }catch (Exception $e){
            DB::rollBack();
            $this->unbuslock($id);
            return ['msg'=>'操作异常！请稍后重试！'];
        }


    }


    //redis加锁
    private function buslock($functions){

        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("business_lock_".$functions, $code);

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