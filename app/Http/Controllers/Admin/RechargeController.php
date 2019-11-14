<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Recharge;
use Illuminate\Http\Request;

class RechargeController extends Controller
{
    /**
     * 数据列表
     */
    public function index(Request $request){
        $recharge=Recharge::query();
        if(true==$request->has('sk_name')){
            $recharge->where('sk_name','like','%'.$request->input('sk_name').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $recharge->whereBetween('creatime',[$start,$end]);
        }
        $data = $recharge->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime'] =date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('recharge.list',['list'=>$data,'input'=>$request->all()]);
    }
    /**
     * 编辑页
     */
    public function edit($id=0){
        $info = $id?Recharge::find($id):[];
        $bank = config('bank');
        $banklist=json_encode($bank);
        return view('recharge.edit',['id'=>$id,'info'=>$info,'banklist'=>$banklist]);
    }
    /**
     * 保存数据
     */
    public function store(StoreRequest $request){
        $data = $request->all();
        unset($data['_token']);
        unset($data['id']);
        $bankcard=Recharge::add_bank($data['sk_banknum']);
        if($bankcard){
            return ['msg'=>'银行卡已添加！'];
        }else{
            $data['creatime']=time();
            $res = Recharge::insert($data);
            if($res){
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }
        }
    }

    /**
     * 删除
     */
    public function destroy($id){
        $info = $id?Recharge::find($id):[];
        if($info['status']==1){
            return ['msg'=>'使用中不能删除！','status'=>0];
        }else{
            $count = Recharge::where('id','=',$id)->delete();
            if ($count){
                return ['msg'=>'删除成功！','status'=>1];
            }else{
                return ['msg'=>'删除失败！','status'=>0];
            }
        }
    }
    /**
     * 启用
     */
    public function enable(StoreRequest $request){
        Recharge::query()->update(array('status'=>0));
        $num = Recharge::where('id','=',$request->input('id'))->update(['status'=>$request->input('status')]);
        if($num){
            return ['msg'=>'启用成功！','status'=>1];
        }else{
            return ['msg'=>'启动失败！','status'=>2];
        }
    }
}