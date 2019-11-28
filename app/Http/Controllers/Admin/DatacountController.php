<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentcount;
use App\Models\Agentdraw;
use App\Models\Buscount;
use App\Models\Busdraw;
use App\Models\Codecount;
use App\Models\Codedraw;
use App\Models\Codeuser;
use App\Models\Qrcode;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DatacountController extends Controller
{
    /*
     * 平台数据统计
     */
    public function index(){
        //订单
        $order=[];
        //商户提现
        $bus=[];
        $busall=Buscount::first(
            array(
                DB::raw('SUM(tol_sore) as tol_sore'),
                DB::raw('SUM(sore_balance) as sore_balance'),
                DB::raw('SUM(tol_sore-sore_balance) as order_profit'),
                DB::raw('SUM(drawMoney) as drawMoney'),
                DB::raw('SUM(balance) as balance'),
                DB::raw('SUM(drawMoney-tradeMoney) as feeMoney'),
            )
        )->toArray();
        $busnone=Busdraw::where('status',0)->sum('money');//提现中

        $order['tol_sore']=$busall['tol_sore']/100; //订单金额
        $order['sore_balance']=$busall['sore_balance']/100; //扣除费率后
        $order['order_profit']=$busall['order_profit']/100; //盈利

        $bus['drawdone']=$busall['drawMoney']/100; // 总提现
        $bus['balance']=$busall['balance']/100; // 余额/未提现
        $bus['drawnone']=$busnone/100; //提现中
        $bus['feemoney']=$busall['feeMoney']/100; //总手续费

        //代理提现
        $agent=[];
        $agentall=Agentcount::first(
            array(
                DB::raw('SUM(drawMoney) as drawMoney'),
                DB::raw('SUM(balance) as balance'),
                DB::raw('SUM(drawMoney-tradeMoney) as feeMoney'),
                DB::raw('SUM(tol_brokerage) as tol_brokerage'),
            )
        )->toArray();
        $agentnone=Agentdraw::where('status',0)->sum('money');

        $agent['drawdone']=$agentall['drawMoney']/100; // 总提现
        $agent['balance']=$agentall['balance']/100; // 余额/未提现
        $agent['drawnone']=$agentnone/100; //提现中
        $agent['feemoney']=$agentall['feeMoney']/100; //总手续费
        $agent['tol_brokerage']=$agentall['tol_brokerage']/100; //总佣金
        //码商统计
        $code=[];

        $codeall=Codecount::first(
            array(
                DB::raw('SUM(tol_brokerage) as tol_brokerage'),
                DB::raw('SUM(drawMoney) as drawMoney'),
                DB::raw('SUM(balance) as balance'),
                DB::raw('SUM(drawMoney-tradeMoney) as feeMoney'),
                DB::raw('SUM(active_money) as active_money'),
                DB::raw('SUM(active_brokerage) as active_brokerage'),
                DB::raw('SUM(active_money-active_brokerage) as active_profit'),
                DB::raw('SUM(tol_recharge) as tol_recharge'),
                DB::raw('SUM(shangfen) as shangfen'),
                DB::raw('SUM(xiafen) as xiafen'),
                DB::raw('SUM(freeze_money) as freeze_money'),
            )
        )->toArray();
        $codenone=Codedraw::where('status',0)->sum('money');

        $code['tol_brokerage']=$codeall['tol_brokerage']/100; // 总佣金

        $code['drawdone']=$codeall['drawMoney']/100; // 总提现
        $code['balance']=$codeall['balance']/100; // 余额/未提现
        $code['drawnone']=$codenone/100; //提现中
        $code['feemoney']=$codeall['feeMoney']/100; //总手续费

        $code['active_money']=$codeall['active_money']/100;//总激活
        $code['active_brokerage']=$codeall['active_brokerage']/100;//总激活佣金
        $code['active_profit']=$codeall['active_profit']/100;//总激活盈利

        $code['tol_recharge']=$codeall['tol_recharge']/100;//总充值
        $code['shangfen']=$codeall['shangfen']/100;//总上分
        $code['xiafen']=$codeall['xiafen']/100;//总下分

        $code['freeze_money']=$codeall['freeze_money']/100;//总冻结金额


        //码商激活
        $codeuser=[];
        $codenum=Codeuser::count();
        $active=Codeuser::where('jh_status',1)->count();
        $erweima=Qrcode::where('status',0)->count();

        $codeuser['codenum']=$codenum;    //码商注册人数
        $codeuser['active']=$active;  //码商激活人数
        $codeuser['erweima']=$erweima;  //二维码未删除
        //数据统计
        $data=[];
        $data['order']=$order;
        $data['bus']=$bus;
        $data['agent']=$agent;
        $data['code']=$code;
        $data['codeuser']=$codeuser;
        return view('datacount.list',['data'=>$data]);
    }


}
