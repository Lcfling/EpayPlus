<?php
/**
created by z
 * time 2019-11-2 16:18:23
 */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Busdraw;
use App\Models\Busdrawreject;
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
        $key='business_lock_'.$id;
        $is=Redis::get($key);
        if(!empty($is)){
            return ['msg'=>'操作失败！'];
        }else{
            $info=Busdraw::find($id);
            $insert=[
                'order_sn'=>$info['order_sn'],
                'business_code'=>$info['business_code'],
                'name'=>$info['name'],
                'deposit_name'=>$info['deposit_name'],
                'deposit_card'=>$info['deposit_card'],
                'money'=>$info['money'],
                'remark'=>$data['remark'],
                'creatime'=>$info['creatime'],
            ];
            $down=Busdraw::reject($id);
            if(!$down){
                return ['msg'=>'操作失败！'];
            }
            $ins=Busdrawreject::insert($insert);
            if(!$ins){
                return ['msg'=>'操作失败！'];
            }else{
                return ['msg'=>'驳回成功！','status'=>1];
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