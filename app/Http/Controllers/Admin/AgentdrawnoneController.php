<?php
/**
created by z
 * time 2019-11-3 9:34:23
 */
namespace App\Http\Controllers\Admin;

use App\Models\Agentbill;
use App\Models\Agentcount;
use App\Models\Agentdraw;

use Illuminate\Http\Request;
use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
class AgentdrawnoneController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $agendraw=Agentdraw::query();
        if(true==$request->has('agent_id')){
            $agendraw->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('order_sn')){
            $agendraw->where('order_sn','=',$request->input('order_sn'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $agendraw->whereBetween('creatime',[$start,$end]);
        }
        $data = $agendraw->where('status','=','0')->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        $min=config('admin.min_date');
        return view('agentdrawnone.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }
    /**
     * 通过
     */
    public function pass(StoreRequest $request){
        $id=$request->input('id');
        $drawinfo=Agentdraw::find($id);
        $islock=$this->agentlock($id);
        if(!$islock){
            return ['msg'=>'请勿频繁操作！'];
        }
        DB::beginTransaction();
        try{
            if(!$draw=Agentdraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
                DB::rollBack();
                $this->unagentlock($id);
                return ['msg'=>'订单已处理！'];
            }
            $status=Agentdraw::pass($id);
            if(!$status){
                DB::rollBack();
                $this->unagentlock($id);
                return ['msg'=>'通过失败！'];
            }
            $drawMoney=$drawinfo['money'];
            $tradeMoney=$drawinfo['tradeMoney'];
            $add=Agentcount::where('business_code',$drawinfo['business_code'])->increment('drawMoney',$drawMoney,['tradeMoney'=>DB::raw("tradeMoney + $tradeMoney")]);
            if(!$add){
                DB::rollBack();
                $this->unagentlock($id);
                return ['msg'=>'更改代理帐户失败！'];
            }
            DB::commit();
            $this->unagentlock($id);
            return ['msg'=>'通过成功！','status'=>1];
        }catch (Exception $e){
            DB::rollBack();
            $this->unagentlock($id);
            return ['msg'=>'操作异常！请稍后重试！'];
        }

    }
    /**
     * 驳回页面
     */
    public function bohui($id){
        $info = $id?Agentdraw::find($id):[];
        $info['creatime']=date("Y-m-d H:i:s",$info['creatime']);
        return view('agentdrawnone.bohui',['id'=>$id,'info'=>$info]);
    }
    /**
     * 驳回
     */
   public function reject(StoreRequest $request){
       $data=$request->all();
       $id=$data['id'];

       $drawinfo=Agentdraw::find($id);

       $weeksuf = computeWeek(time(),false);
       $agentbill=new Agentbill();
       $agentbill->setTable('agent_billflow_'.$weeksuf);

       $islock=$this->unagentlock($id);
       if(!$islock){
           return ['msg'=>'请勿频繁操作！'];
       }
       DB::beginTransaction();
       try{
           if(!$draw=Agentdraw::where(array('id'=>$id,'status'=>0))->lockForUpdate()->first()){
               DB::rollBack();
               $this->unagentlock($id);
               return ['msg'=>'订单已处理！'];
           }
           $status=Agentdraw::reject($id,$data['remark']);
           if(!$status){
               DB::rollBack();
               $this->unagentlock($id);
               return ['msg'=>'驳回失败！'];
           }
           $bill=[
               'order_sn'=>$drawinfo['order_sn'],
               'agent_id'=>$drawinfo['agent_id'],
               'score'=>$drawinfo['money'],
               'status'=>3,
               'remark'=>'代理提现驳回',
               'creatime'=>time()
           ];
           $ins=$agentbill->insert($bill);
           if(!$ins){
               DB::rollBack();
               $this->unagentlock($id);
               return ['msg'=>'代理流水添加失败！'];
           }
           $drawMoney=$drawinfo['money'];
           $tradeMoney=$drawinfo['tradeMoney'];
           $reduce=Agentcount::where('business_code',$drawinfo['business_code'])->increment('balance',$drawMoney);
           if(!$reduce){
               DB::rollBack();
               $this->unagentlock($id);
               return ['msg'=>'更改代理帐户失败！'];
           }
           DB::commit();
           $this->unagentlock($id);
           return ['msg'=>'驳回成功！','status'=>1];
       }catch (Exception $e){
           DB::rollBack();
           $this->unagentlock($id);
           return ['msg'=>'操作异常！请稍后重试！'];
       }


   }

    //redis加锁
    private function agentlock($functions){
        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("agent_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("agent_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }
    //redis解锁
    private function unagentlock($functions){
        Redis::del("agent_lock_".$functions);
    }
}
