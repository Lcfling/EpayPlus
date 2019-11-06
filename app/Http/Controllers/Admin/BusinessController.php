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
        if(true==$request->has('account')){
            $map[]=['account','like','%'.$request->input('account').'%'];
        }
        if(true==$request->has('nickname')){
            $map[]=['nickname','like','%'.$request->input('nickname').'%'];
        }
        $data = Business::where($map)->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['updatetime']=date("Y-m-d H:i:s",$value["updatetime"]);
        }
        return view('business.list',['list'=>$data,'input'=>$request->all()]);
    }

    /**
     * 添加/编辑页
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
            $data['password']=bcrypt($data['password']);
            $data['remember_token']='';
            $data['paypassword']='';
            $unicode=$this->unicode();
            $accessKey=bcrypt(md5(md5($unicode)));
            $data['accessKey']=$accessKey;
            $data['creatime']=time();
            $data['updatetime']=time();
            $insertID=Business::insertGetId($data);
            if($insertID){
                $agent['business_code']=$insertID;
                $agent['creatime']=time();
                $res3=DB::table('agent_fee')->insert($agent);
                if($res3){
                    return ['msg'=>'添加成功！','status'=>1];
                }else{
                    return ['msg'=>'添加失败！'];
                }

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
            if($res!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }

    }
    /**
     * 费率编辑页
     */
    public function busfee($bussiness_code){
        $info = $bussiness_code?Business::find($bussiness_code):[];
        $fee=DB::table('agent_fee')->where('business_code','=',$bussiness_code)->first();
        $fee=get_object_vars($fee);
        return view('business.editfee',['id'=>$bussiness_code,'info'=>$info,'fee'=>$fee]);
    }
    /**
     * 费率更改
     */
    public function busnewfee(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $fee=floatval($data['fee']);
        $busfee=Business::where('business_code','=',$id)->update(array('fee'=>$fee));
        $agent1_id=$data['agent1_id'];
        $agent1_fee=$data['agent1_fee'];
        $agent2_id=$data['agent2_id'];
        $agent2_fee=$data['agent2_fee'];
        if($agent1_id!=null&&$agent2_id!=null){
            if($agent1_id==$agent2_id){
                return ['msg'=>'一、二级代理商不可相同！'];
            }else{
                $res1=Business::is_agent($agent1_id);
                $res2=Business::is_agent($agent2_id);
                if($res1==false){
                    return ['msg'=>'一级代理商不存在！'];
                }else if($res2==false){
                    return ['msg'=>'二级代理商不存在！'];
                }else{
                    if(!preg_match("/^[0-9]+(.?[0-9]{1,2})?$/", $agent1_fee)){
                        return ['msg'=>'请输入正确一级费率！'];
                    }else if(!preg_match("/^[0-9]+(.?[0-9]{1,2})?$/", $agent2_fee)){
                        return ['msg'=>'请输入正确二级费率！'];
                    }else{
                        $agent1_fee=floatval($agent1_fee);
                        $agent2_fee=floatval($agent2_fee);
                        if($agent1_fee>=$fee){
                            return ['msg'=>'一级费率不可大于商户费率！'];
                        }else if($agent2_fee>=$agent1_fee){
                            return ['msg'=>'二级费率不可大于一级费率！'];
                        }else{
                            $fee=[
                                'agent1_id'=>intval($agent1_id),
                                'agent1_fee'=>$agent1_fee,
                                'agent2_id'=>intval($agent2_id),
                                'agent2_fee'=>$agent2_fee,
                            ];
                            $up1=DB::table('agent_fee')->where('business_code','=',$id)->update($fee);
                            if($up1!==false&&$busfee!==false){
                                return ['msg'=>'修改成功！','status'=>1];
                            }else{
                                return ['msg'=>'修改失败！'];
                            }
                        }

                    }
                }
            }

        }else if($agent1_id!=null&&$agent2_id==null){
            $res2=Business::is_agent($agent1_id);
            if($res2==true){
                if(preg_match("/^[0-9]+(.?[0-9]{1,2})?$/", $agent1_fee)){
                    $agent1_fee=floatval($agent1_fee);
                    if($agent1_fee>=$fee){
                        return ['msg'=>'一级费率不可大于商户费率！'];
                    }else{
                        $fee=[
                            'agent1_id'=>intval($agent1_id),
                            'agent1_fee'=>$agent1_fee,
                        ];
                        $up2=DB::table('agent_fee')->where('business_code','=',$id)->update($fee);
                        if($up2!==false&&$busfee!==false){
                            return ['msg'=>'修改成功！','status'=>1];
                        }else{
                            return ['msg'=>'修改失败！'];
                        }
                    }

                }else{
                    return ['msg'=>'请输入正确一级费率！'];
                }
            }else{
                return ['msg'=>'一级代理商不存在！'];
            }
        }else if($agent2_id!=null&&$agent1_id==null){
            return ['msg'=>'请先填写一级代理和费率！'];
        }else{
            if($busfee!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }

    }
    /**
     * 登录密码页
     */
    public function buspwd($bussiness_code){
        $info = $bussiness_code?Business::find($bussiness_code):[];
        return view('business.editpwd',['id'=>$bussiness_code,'info'=>$info]);
    }
    /**
     * 修改登录密码
     */
    public function busnewpwd(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $pwd=bcrypt($data['password']);
        $res=Business::where('business_code',$id)->update(array('password'=>$pwd,'updatetime'=>time()));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }
    /**
     * 支付密码页
     */
    public function buspayword($bussiness_code){
        $info = $bussiness_code?Business::find($bussiness_code):[];
        return view('business.editpayword',['id'=>$bussiness_code,'info'=>$info]);
    }
    /**
     * 修改支付密码
     */
    public function busnewpayword(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['_token']);
        unset($data['id']);
        $payword=md5(md5($data['paypassword']));
        $res=Business::where('business_code',$id)->update(array('paypassword'=>$payword,'updatetime'=>time()));
        if($res!==false){
            return ['msg'=>'修改成功！','status'=>1];
        }else{
            return ['msg'=>'修改失败！'];
        }
    }

    //生成6位随机码
    private function unicode(){
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 6;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return $d;

    }

}