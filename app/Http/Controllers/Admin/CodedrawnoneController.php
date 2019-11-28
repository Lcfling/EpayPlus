<?php
/**
created by z
 * time 2019-11-3 10:14:52
 */
namespace App\Http\Controllers\Admin;

use App\Models\Billflow;
use App\Models\Codecount;
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
        if(true==$request->has('order_sn')){
            $codedraw->where('order_sn','=',$request->input('order_sn'));
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
        $min=config('admin.min_date');
        return view('codedrawnone.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }
    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $drawinfo=Codedraw::find($id);
        $islock=$this->codelock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            if(!$draw=Codedraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'订单已处理！'];
            }
            $status=Codedraw::pass($id);
            if(!$status){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'通过失败！'];
            }
            $drawMoney=$drawinfo['money'];
            $tradeMoney=$drawinfo['tradeMoney'];
            $add=Codecount::where('user_id',$drawinfo['user_id'])->increment('drawMoney',$drawMoney,['tradeMoney'=>DB::raw("tradeMoney + $tradeMoney")]);
            if(!$add){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'更改代理帐户失败！'];
            }
            DB::commit();
            $this->uncodelock($id);
            return ['msg'=>'通过成功！','status'=>1];
        }catch (Exception $e){
            DB::rollBack();
            $this->uncodelock($id);
            return ['msg'=>'操作异常！请稍后重试！'];
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

        $drawinfo=Codedraw::find($id);

        $tablepfe=date('Ymd');
        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);

        $islock=$this->codelock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            if(!$draw=Codedraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'订单已处理！'];
            }
            $status=Codedraw::reject($id,$data['remark']);
            if(!$status){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'驳回失败！'];
            }
            $bill=[
                'order_sn'=>$drawinfo['order_sn'],
                'user_id'=>$drawinfo['user_id'],
                'score'=>$drawinfo['money'],
                'status'=>6,
                'remark'=>'码商提现驳回',
                'creatime'=>time()
            ];
            $ins=$account->insert($bill);
            if(!$ins){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'码商流水添加失败！'];
            }
            $drawMoney=$drawinfo['money'];
            $tradeMoney=$drawinfo['tradeMoney'];
            $reduce=Codecount::where('user_id',$drawinfo['user_id'])->increment('balance',$drawMoney);
            if(!$reduce){
                DB::rollBack();
                $this->uncodelock($id);
                return ['msg'=>'更改码商帐户失败！'];
            }
            DB::commit();
            $this->uncodelock($id);
            return ['msg'=>'驳回成功！','status'=>1];
        }catch (Exception $e){
            DB::rollBack();
            $this->uncodelock($id);
            return ['msg'=>'操作异常！请稍后重试！'];
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
