<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Models\Agentbill;
use App\Models\Agentcount;
use App\Models\Agentdraw;
use App\Models\Billflow;
use App\Models\Busbill;
use App\Models\Buscount;
use App\Models\Busdraw;
use App\Models\Codecount;
use App\Models\Codedraw;
use App\Models\Codeuser;
use App\Models\Qrcode;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DaycountController extends Controller
{
    /*
     * 平台单日数据统计
     */
    public function index(StoreRequest $request){

        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $time = strtotime($creatime);
            $weeksuf = computeWeek($time,false);
            $tablepfe = date('Ymd',$time);

        }else{
            $creatime=date('Y-m-d');
            $weeksuf = computeWeek(time(),false);
            $tablepfe=date('Ymd');
        }
        $start=strtotime($creatime);
        $end=strtotime('+1day',$start);

        //订单
        $ordernum=[];

        $busbill=new Busbill();
        $busbill->setTable('business_billflow_'.$weeksuf);

        $orderall=$busbill->where('status',1)->whereBetween('creatime',[$start,$end])->first(
            array(
                DB::raw('SUM(tradeMoney) as sk_money'),
                DB::raw('SUM(score) as tradeMoney'),
                DB::raw('SUM(tradeMoney-score) as order_profit'),
            )
        )->toArray();

        $ordernum['tol_sore']=($orderall['sk_money']?$orderall['sk_money']:0)/100; //订单金额
        $ordernum['sore_balance']=($orderall['tradeMoney']?$orderall['tradeMoney']:0)/100; //收款金额(扣除费率)
        $ordernum['order_profit']=($orderall['order_profit']?$orderall['order_profit']:0)/100; //盈利

        //商户提现
        $bus=[];
        $busbalance=Buscount::sum('balance');//余额

        $busdraw=Busdraw::where('status',1)->whereBetween('creatime',[$start,$end])->first(
            array(
                DB::raw('SUM(money) as money'),
                DB::raw('SUM(money-tradeMoney) as feeMoney'),
            )
        )->toArray();
        $busnone=Busdraw::where('status',0)->whereBetween('creatime',[$start,$end])->sum('money');//提现中

        $bus['drawdone']=$busdraw['money']/100; // 总提现
        $bus['balance']=$busbalance/100; // 余额/未提现
        $bus['drawnone']=$busnone/100; //提现中
        $bus['feemoney']=$busdraw['feeMoney']/100; //总手续费

        //代理提现
        $agent=[];
        $agentbalance=Agentcount::sum('balance');

        $agentdraw=Agentdraw::where('status',1)->whereBetween('creatime',[$start,$end])->first(
            array(
                DB::raw('SUM(money) as money'),
                DB::raw('SUM(money-tradeMoney) as feeMoney'),
            )
        )->toArray();
        $agentnone=Agentdraw::where('status',0)->whereBetween('creatime',[$start,$end])->sum('money');

        $agent['drawdone']=$agentdraw['money']/100; // 总提现
        $agent['balance']=$agentbalance/100; // 余额/未提现
        $agent['drawnone']=$agentnone/100; //提现中
        $agent['feemoney']=$agentdraw['feeMoney']/100; //总手续费
        //代理佣金
        $agentbill=new Agentbill;
        $agentbill->setTable('agent_billflow_'.$weeksuf);

        $agent_fanyong=$agentbill->where('status',2)->whereBetween('creatime',[$start,$end])->sum('score');
        $agent['tol_brokerage']=$agent_fanyong/100;

        //码商提现
        $code=[];
        $codebalance=Codecount::sum('balance');//余额

        $codedraw=Codedraw::where('status',1)->whereBetween('creatime',[$start,$end])->first(
            array(
                DB::raw('SUM(money) as money'),
                DB::raw('SUM(money-tradeMoney) as feeMoney'),
            )
        )->toArray();

        $codenone=Codedraw::where('status',0)->whereBetween('creatime',[$start,$end])->sum('money');

        $code['drawdone']=$codedraw['money']/100; // 总提现
        $code['balance']=$codebalance/100; // 余额/未提现
        $code['drawnone']=$codenone/100; //提现中
        $code['feemoney']=$codedraw['feeMoney']/100; //总手续费


        $account =new Billflow;
        $account->setTable('account_'.$tablepfe);

        $tol_brokerage=$account->where('status',5)->sum('score');//码商总支付佣金
        $active=$account->where('status',7)->sum('score');//码商总激活费用
        $active_brokerage=$account->where('status',8)->sum('score');//码商总激活返佣
        $recharge=$account->where('status',1)->sum('score');//总充值
        $shangfen=$account->where('status',9)->sum('score');//总上分
        $xiafen=$account->where('status',10)->sum('score');//总下分
        $freeze=$account->whereIn('status',[3,4])->sum('score');//总冻结

        $active=abs($active);
        $xiafen=abs($xiafen);
        $freeze=abs($freeze);

        $code['tol_brokerage']=$tol_brokerage/100;  //总支付佣金

        $code['active_money']=$active/100;  //激活费用
        $code['active_brokerage']=$active_brokerage/100;//激活返佣
        $code['active_profit']=($active-$active_brokerage)/100;//总激活盈利


        $code['tol_recharge']=$recharge/100;//总充值
        $code['shangfen']=$shangfen/100;//总上分
        $code['xiafen']=$xiafen/100;//总下分

        $code['freeze_money']=$freeze/100;//总冻结


        //码商激活
        $codeuser=[];
        $codenum=Codeuser::whereBetween('reg_time',[$start,$end])->count();
        $active=Codeuser::where('jh_status',1)->whereBetween('jh_time',[$start,$end])->count();
        $erweima=Qrcode::where('status',0)->whereBetween('creatime',[$start,$end])->count();

        $codeuser['codenum']=$codenum;    //码商注册人数
        $codeuser['active']=$active;  //码商激活人数
        $codeuser['erweima']=$erweima;  //二维码未删除

        //数据统计
        $data=[];
        $data['order']=$ordernum;
        $data['bus']=$bus;
        $data['agent']=$agent;
        $data['code']=$code;
        $data['codeuser']=$codeuser;
        $min=config('admin.min_date');
        return view('daycount.list',['data'=>$data,'min'=>$min,'input'=>$request->all()]);
    }


}
