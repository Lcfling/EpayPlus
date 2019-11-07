<?php
/**
created by z
 * time 2019-11-01 16:40:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Coderakemoney;
class CoderakemoneyController extends Controller
{
    /**
     * 佣金配置
     */
    public function index(){
        $data=Coderakemoney::get()->toArray();
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('coderakemoney.list',['list'=>$data]);
    }

    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Coderakemoney::find($id):[];
        return view('coderakemoney.edit',['id'=>$id,'info'=>$info]);
    }
    /**
     * 保存
     */
    public function update(StoreRequest $request){
        $id =$request->input('id');
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $jh=$data['jhmoney'];
        $fy1=$data['fymoney1'];
        $fy2=$data['fymoney2'];
        $fy3=$data['fymoney3'];
        $fy4=$data['fymoney4'];
        $fy5=$data['fymoney5'];
        $fy6=$data['fymoney6'];
        $fy7=$data['fymoney7'];
        $fy8=$data['fymoney8'];
        $fy9=$data['fymoney9'];
        $fy10=$data['fymoney10'];
        $fysum=$fy1+$fy2+$fy3+$fy4+$fy5+$fy6+$fy7+$fy8+$fy9+$fy10;
        if($fysum>$jh){
            return ['msg'=>'返佣总金额可不大于激活金额！'];
        }else{
            $moeney=[
                'jhmoney'=>$jh*100,
                'fymoney1'=>$fy1*100,
                'fymoney2'=>$fy2*100,
                'fymoney3'=>$fy3*100,
                'fymoney4'=>$fy4*100,
                'fymoney5'=>$fy5*100,
                'fymoney6'=>$fy6*100,
                'fymoney7'=>$fy7*100,
                'fymoney8'=>$fy8*100,
                'fymoney9'=>$fy9*100,
                'fymoney10'=>$fy10*100,
            ];
            $update=Coderakemoney::where('id',$id)->update($moeney);
            if($update!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }

    }
}
