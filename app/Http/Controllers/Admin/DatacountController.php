<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agentcount;
use App\Models\Agentdraw;
use App\Models\Billflow;
use App\Models\Buscount;
use App\Models\Busdraw;
use App\Models\Codecount;
use App\Models\Codedraw;
use App\Models\Rechargelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;

class DatacountController extends Controller
{
    /*
     * 平台数据统计
     */
    public function index(){

        //商户提现
        $bus=[];
        $busall=Buscount::first(
            array(
                DB::raw('SUM(drawMoney) as drawMoney'),
                DB::raw('SUM(balance) as balance'),
                DB::raw('SUM(drawMoney-tradeMoney) as feeMoney'),
            )
        )->toArray();
        $busnone=Busdraw::where('status',0)->sum('money');//提现中

        $bus['done']=$busall['drawMoney']/100;
        $bus['balance']=$busall['balance']/100;
        $bus['none']=$busnone/100;
        //代理提现
        $agent=[];
        $agentdone=Agentdraw::where('status',1)->sum('money');
        $agentbalance=Agentcount::count('balance');
        $agentnone=Agentdraw::where('status',0)->sum('money');
        $agent['done']=$agentdone/100;
        $agent['balance']=$agentbalance/100;
        $agent['none']=$agentnone/100;
        //码商提现
        $code=[];
        $codedone=Codedraw::where('status',1)->sum('money');
        $codebalance=Codecount::count('balance');
        $codenone=Codedraw::where('status',0)->sum('money');
        $code['done']=$codedone/100;
        $code['balance']=$codebalance/100;
        $code['none']=$codenone/100;

        //码商充值
        $code_charge_done=Rechargelist::where('status',1)->sum('score');
        $res=$this->getdaybill(date('Ymd'));
        $res['code_charge']['done']=$code_charge_done/100;
        //数据统计
        $data=[];
        $data['bus']=$bus;
        $data['agent']=$agent;
        $data['code']=$code;
        $data['code_charge']=$res['code_charge'];
        return view('datacount.list',['data'=>$data]);
    }


    //获取哪一天流水表数据
    protected function getdaybill($date){
        $table='account_'.$date;
        if(!Schema::hasTable($table)){
            return false;
        }
        $account =new Billflow;
        $account->setTable($table);
        $code_charge=[];
        $code_charge_shangfen=$account->where('status','=',1)->where('remark','like','%手动上分%')->sum('score');
        $code_charge_xiafen=$account->where('status','=',1)->where('remark','like','%手动下分%')->sum('score');
        $code_charge['shangfen']=$code_charge_shangfen/100;
        $code_charge['xiafen']=abs($code_charge_xiafen)/100;

        //数据统计
        $data=[];
        $data['code_charge']=$code_charge;
        return $data;
    }

}
