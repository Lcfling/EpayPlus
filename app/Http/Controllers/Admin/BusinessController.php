<?php
/**
created by z
 * time 2019-10-31 14:02:03
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Business;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    /**
     * 数据列表
     */
    public function index(StoreRequest $request){
        $map=array();
        if(true==$request->has('business_code')){
            $map['business_code']=$request->input('business_code');
        }
        $data = Business::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['price'] = DB::table('order')->where('business_code',$data[$key]['business_code'])->where('status',1)->sum('payMoney');
            $data[$key]['money'] = DB::table('business_withdraw')->where('business_code',$data[$key]['business_code'])->where('status',1)->sum('money');
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
        }
        return view('business.list',['list'=>$data,'input'=>$request->all()]);
    }

    /**
     * 编辑页
     */
    public function edit($bussiness_code=0){
        $info = $bussiness_code?Business::find($bussiness_code):[];
        return view('business.edit',['id'=>$bussiness_code,'info'=>$info]);
    }
    /**
     * 添加保存数据
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        unset($data['_token']);
        unset($data['id']);
        $account=$data['account'];
        $mobile=$data['mobile'];
        $res1=Business::add_account($account);
        $res2=Business::add_mobile($mobile);
        if($res1){
            return ['msg'=>'账号已存在！'];
        }else if($res2){
            return ['msg'=>'手机号已存在！'];
        }else{
            $data['accessKey']=md5($data['accessKey']);
            $data['creatime']=time();
            $res=Business::insert($data);
            if($res){
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }
        }

    }
    /**
     * 编辑保存数据
     */
    public function update(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $account=$data['account'];
        $mobile=$data['mobile'];
        $res1=Business::edit_account($id,$account);
        $res2=Business::edit_mobile($id,$mobile);
        if($res1){
            return ['msg'=>'账号已存在！'];
        }else if($res2){
            return ['msg'=>'手机号已存在！'];
        }else{
            $data['accessKey']=md5($data['accessKey']);
            $res=Business::where('business_code',$id)->update($data);
            if($res){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }

    }

    /**
     * 删除
     */
    public function destroy($id){
        $res = Business::where('business_code',$id)->delete();
        if($res==1){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！'];
        }
    }

}