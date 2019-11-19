<?php
/**
created by z
 * time 2019-11-3 10:14:52
 */
namespace App\Http\Controllers\Admin;

use App\Models\Codedraw;
use App\Models\Codedrawreject;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class CodedrawnoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $codedraw=Codedraw::query();
        if(true==$request->has('user_id')){
            $codedraw->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('order_no')){
            $codedraw->where('order_no','=',$request->input('order_no'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $codedraw->whereBetween('creatime',[$start,$end]);
        }
        $data = $codedraw->where('status','=','0')->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('codedrawnone.list',['list'=>$data,'input'=>$request->all()]);

    }
    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $islock=$this->codelock($id);
        if($islock){
            $res=Codedraw::pass($id);
            if($res){
                $this->uncodelock($id);
                return ['msg'=>'通过成功！','status'=>1];
            }else{
                $this->uncodelock($id);
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
        $info = $id?Codedraw::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        return view('codedrawnone.bohui',['id'=>$id,'info'=>$info]);
    }
    /**
     * 驳回
     */
    public function reject(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        $key='code_lock_'.$id;
        $is=Redis::get($key);
        if(!empty($is)){
            return ['msg'=>'操作失败！'];
        }else{
            $info=Codedraw::find($id);
            $insert=[
                'order_sn'=>$info['order_sn'],
                'user_id'=>$info['user_id'],
                'name'=>$info['name'],
                'wx_name'=>$info['wx_name'],
                'mobile'=>$info['mobile'],
                'deposit_name'=>$info['deposit_name'],
                'deposit_card'=>$info['deposit_card'],
                'money'=>$info['money'],
                'remark'=>$data['remark'],
                'creatime'=>$info['creatime'],
            ];
            $down=Codedraw::reject($id);
            if(!$down){
                return ['msg'=>'操作失败！'];
            }
            $ins=Codedrawreject::insert($insert);
            if(!$ins){
                return ['msg'=>'操作失败！'];
            }else{
                return ['msg'=>'驳回成功！','status'=>1];
            }

        }

    }

    //redis加锁
    private function codelock($functions){
        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("code_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("code_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }
    //redis解锁
    private function uncodelock($functions){
        Redis::del("code_lock_".$functions);
    }
}
