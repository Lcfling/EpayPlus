<?php

namespace App\Http\Controllers\Admin;

use App\Models\Buscount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuscountController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $buscount=Buscount::query();
        if(true==$request->has('business_code')){
            $buscount->where('business_code','=',$request->input('business_code'));
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $buscount->whereBetween('creatime',[$start,$end]);
        }
        if(true==$request->has('savetime')){
            $creatime=$request->input('savetime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $buscount->whereBetween('savetime',[$start,$end]);
        }
        if(true==$request->input('excel')&& true==$request->has('excel')){
            $head = array('商户ID','余额','总分','创建时间','更新时间');
            $excel = $buscount->select('business_code','balance','tol_sore','creatime','savetime')->get()->toArray();
            foreach ($excel as $key=>$value){
                $excel[$key]['balance']=$value['balance']/100;
                $excel[$key]['tol_sore']=$value['tol_sore']/100;
                $excel[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
                $excel[$key]['savetime']=date("Y-m-d H:i:s",$value["savetime"]);
            }
            exportExcel($head,$excel,'商户账单'.date('YmdHis',time()),'',true);
        }else{
            $data = $buscount->orderBy('creatime','desc')->paginate(10)->appends($request->all());
            foreach ($data as $key =>$value){
                $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
                $data[$key]['savetime'] =date("Y-m-d H:i:s",$value["savetime"]);
            }
        }

        return view('buscount.list',['list'=>$data,'input'=>$request->all()]);

    }
}
