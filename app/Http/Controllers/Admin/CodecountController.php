<?php

namespace App\Http\Controllers\Admin;

use App\Models\Codecount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodecountController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $codecount=Codecount::query();
        if(true==$request->has('user_id')){
            $codecount->where('agent_id','=',$request->input('agent_id'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $codecount->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('savetime')){
            $creatime=$request->input('savetime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $codecount->whereBetween('savetime',[$start,$end]);
        }if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('码商ID','余额','总分','总佣金','冻结金额','创建时间','更新时间');
            $excel = $codecount->select('user_id','balance','tol_sore','tol_brokerage','freeze_money','creatime','savetime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['balance']=$value['balance']/100;
                $excel[$key]['tol_sore']=$value['tol_sore']/100;
                $excel[$key]['tol_brokerage']=$value['tol_brokerage']/100;
                $excel[$key]['freeze_money']=$value['freeze_money']/100;
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
                $excel[$key]['savetime']=date("Y-m-d H:i:s",$value["savetime"]);
            }
            exportExcel($head,$excel,'码商账单'.date('YmdHis',time()),'',true);
        }else{
            $data = $codecount->orderBy('creatime','desc')->paginate(10)->appends($request->all());
            foreach ($data as $key =>$value){
                $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
                $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
            }
        }

        return view('codecount.list',['list'=>$data,'input'=>$request->all()]);

    }
}
