<?php

namespace App\Http\Controllers\Admin;

use App\Models\Busbill;
use App\Models\Buscount;
use App\Models\Busdraw;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BusdaycountController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){

        if(true==$request->has('creatime')){
            $time = strtotime($request->input('creatime'));
            $weeksuf = computeWeek($time,false);
        }else{
            $time=date('Y-m-d');
            $weeksuf = computeWeek(time(),false);
        }
        $busbill=new Busbill();
        $table='business_billflow_'.$weeksuf;
        $busbill->setTable($table);

        if(true==$request->has('business_code')){
            $busbill->where('business_count.business_code','=',$request->input('business_code'));
        }

        $data = $busbill->leftJoin('business',$table.'.business_code','=','business.business_code')
                         ->select($table.'.*','business.nickname','business.mobile','business.fee')
                         ->orderBy($table.'.business_code','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $res=$this->daybill($weeksuf,$time,$data[$key]['business_code']);
            $data[$key]['done_rate']=$res['done_rate'];//成功率
            $data[$key]['sk_money']=$res['sk_money'];//收款金额
            $data[$key]['tradeMoney']=$res['tradeMoney'];//实收金额
            $data[$key]['profit']=$res['profit'];//收获盈利
            $data[$key]['draw_money']=$res['draw_money'];//提现金额
            $data[$key]['trade_Money']=$res['tradeMoney'];//到账金额
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }

        $min=config('admin.min_date');
        return view('busdaycount.list',['list'=>$data,'min'=>$min,'input'=>$request->all()]);

    }

    protected function daybill($weeksuf,$creatime,$business_code){
        $data=[];
        $busbill=new Busbill();
        $busbill->setTable('business_billflow_'.$weeksuf);
        $money=$busbill->where(array('business_code'=>$business_code,'status'=>1))->first(
            array(
                DB::raw('SUM(score) as sk_money'),
                DB::raw('SUM(tradeMoney) as tradeMoney'),
                DB::raw('SUM(score-tradeMoney) as profit'),
            )
        )->toArray();
        $data['sk_money']=($money['sk_money']?$money['sk_money']:0)/100;//收款总额
        $data['tradeMoney']=($money['tradeMoney']?$money['tradeMoney']:0)/100;//实收金额
        $data['profit']=($money['profit']?$money['profit']:0)/100;//收获盈利

        $start=strtotime($creatime);
        $end=strtotime('+1day',$start);
        $draw=Busdraw::where(array('business_code'=>$business_code,'status'=>1))->whereBetween('creatime',[$start,$end])->first(
            array(
                DB::raw('SUM(money) as money'),
                DB::raw('SUM(tradeMoney) as tradeMoney'),
            )
        )->toArray();
        $data['draw_money']=($draw['money']?$draw['money']:0)/100;
        $data['tradeMoney']=($draw['tradeMoney']?$draw['tradeMoney']:0)/100;


        $order=new Order;
        $order->setTable('order_'.$weeksuf);

        $total=$order->where('business_code',$business_code)->whereBetween('creatime',[$start,$end])->count('order_sn');//今日全部订单
        $done=$order->where(array('business_code'=>$business_code,'status'=>1))->whereBetween('creatime',[$start,$end])->count('order_sn');//今日成功订单

        if($total==0){
            $data['done_rate']=0;
        }else{
            $data['done_rate']=round($done/$total*100,2);
        }
        return $data;

    }
}
