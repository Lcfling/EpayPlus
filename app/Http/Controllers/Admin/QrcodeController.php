<?php
/**
created by z
 * time 2019-11-4 17:53:05
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Qrcode;

class QrcodeController extends Controller
{
    /**
     * 二维码列表
     */
    public function index(StoreRequest $request){
        $erweima=Qrcode::query();

        if(true==$request->has('id')){
            $erweima->where('id','=',$request->input('id'));
        }

        if(true==$request->has('user_id')){
            $erweima->where('user_id','=',$request->input('user_id'));
        }
        if(true==$request->has('nickname')){
            $erweima->where('nickname','like','%'.$request->input('nickname').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $erweima->whereBetween('creatime',[$start,$end]);
        }
        $data = $erweima->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['erweima']=config('admin.zhu_img').$value["erweima"];
        }
        $qrcode=[];
        $open=Qrcode::where(array('status'=>0,'code_status'=>0))->count();
        $no_del=Qrcode::where('status',0)->count();
        $all=Qrcode::count();
        $qrcode['open']=$open;
        $qrcode['no_del']=$no_del;
        $qrcode['all']=$all;
        $min=config('admin.min_date');
        return view('qrcode.list',['list'=>$data,'qrcode'=>$qrcode,'min'=>$min,'input'=>$request->all()]);
    }
}
