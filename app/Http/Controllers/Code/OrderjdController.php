<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/7
 * Time: 10:42
 */
namespace App\Http\Controllers\Code;
use App\Models\Accountlog;
use App\Models\Business;
use App\Models\Czinfo;
use App\Models\Czrecord;
use App\Models\Erweima;
use App\Models\Order;
use App\Models\Orderrecord;
use App\Models\Rebate;
use App\Models\Userscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class OrderjdController extends CommonController {

    // 在线充值
    public function chongzhi(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $username = htmlformat($_POST['name']);
            //姓名
            $score = (int)($_POST['score']*100);
            //金额
            $sk_name = htmlformat($_POST['sk_name']);
            //收款银行姓名
            $sk_bankname = htmlformat($_POST['sk_bankname']);
            // 收款银行名称
            $sk_banknum = htmlformat($_POST['sk_banknum']);
            //收款银行卡号
            $code=time().rand(100000,999999);
            file_put_contents('./FileType.txt',print_r($_POST,true).PHP_EOL,FILE_APPEND);
            Redis::del("app_recharge".$user_id);
            //随机锁入队
            Redis::rPush('app_recharge'.$user_id,$code);
            //随机锁出队
            $codes=Redis::LINDEX("app_recharge".$user_id,0);
            if ($code != $codes) {
                Redis::del("app_recharge".$user_id);
                ajaxReturn(null, "数据信息异常",0);
            }
            if ($score<=0) {
                Redis::del("app_recharge".$user_id);
                ajaxReturn(null, "金额不能小于0",0);
            }
            if (empty($username)  ||  empty($score) ||empty($sk_name) ||empty($sk_bankname) || empty($sk_banknum)) {
                Redis::del("app_recharge".$user_id);
                ajaxReturn(null, "数据信息异常",0);
            }
            $czrecordinfo=Czrecord::where(array("user_id"=>$user_id,"status"=>0))->first();
            if (!empty($czrecordinfo)) {
                Redis::del("app_recharge".$user_id);
                ajaxReturn(null, "当前有订单未审核",0);
            }
            //限定格式为jpg,jpeg,png
            $fileTypes = ['jpeg', 'jpg','png'];
            if ($request->hasFile('uploadfile')) {
                foreach ($request->file('uploadfile') as $file) {
                    if ($file->isValid()) { //判断文件上传是否有效
                        $FileType = $file->getClientOriginalExtension(); //获取文件后缀
                        file_put_contents('./FileType.txt',$FileType.PHP_EOL,FILE_APPEND);
                        if (!in_array($FileType, $fileTypes)) {
                            ajaxReturn("","图片格式为jpg,png,jpeg",0);
                        }
                        $FilePath = $file->getRealPath(); //获取文件临时存放位置
                        file_put_contents('./FileType.txt',$FilePath.PHP_EOL,FILE_APPEND);
                        $FileName = date('YmdHis') . uniqid() . '.' . $FileType; //定义文件名
                        file_put_contents('./FileType.txt',$FileName.PHP_EOL,FILE_APPEND);
                        Storage::disk('recharge')->put($FileName, file_get_contents($FilePath)); //存储文件
                    }
                    $data =array(
                        'user_id'=>$user_id,
                        'name'=>$username,
                        'score'=>$score,
                        'czimg'=>"/recharge/" . $FileName,
                        'status'=>0,
                        'sk_name'=>$sk_name,
                        'sk_bankname'=>$sk_bankname,
                        'sk_banknum'=>$sk_banknum,
                        'creatime'=>time()
                    );
                    file_put_contents('./FileType.txt',print_r($data,true).PHP_EOL,FILE_APPEND);
                    $status = Czrecord::insert($data);
                    if ($status) {
                        Redis::del("app_recharge".$user_id);
                        ajaxReturn("", "上传成功");
                    } else {
                        Redis::del("app_recharge".$user_id);
                        ajaxReturn("", "上传失败!", 0);
                    }
                }
            } else {
                Redis::del("app_recharge".$user_id);
                ajaxReturn("","未上传图片!",0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 在线充值记录
     */
    public function chongzhirecord(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $lastid = $request->input('lastid');
            if($lastid){
                $where =[['user_id',$user_id],['id','<',$lastid]];
            }else{
                $where =array('user_id'=>$user_id);
            }
            $czrecord=Czrecord::where($where)->orderBy('creatime','desc')->limit(10)->get();
            foreach ($czrecord as &$v) {
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['score']=  $v['score']/100;
            }
            ajaxReturn($czrecord, "在线充值记录");
        } else {
            ajaxReturn('','请求数据异常!',0);
        }

    }
    /**
     * 充值信息列表
     */
    public function czlist() {
        $czinfo=Czinfo::where(array("status"=>1))->get();
        ajaxReturn($czinfo,"充值信息");
    }
    /**
     * 获取详细充值信息
     */
    public function chongzhiinfo(Request $request) {
        if($request->isMethod('post')) {
            if($request->has('id')){
                $id =$request->input('id');
            }else{
                ajaxReturn('','请求数据异常!',0);
            }
            $czinfo=Czinfo::where(array("id"=>$id))->first();
            ajaxReturn($czinfo,"充值信息");
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 进行中的订单
     */
    public function ordering(Request $request) {
        if($request->isMethod('post')) {
            $user_id =$this->uid;
            $order_info=Orderrecord::where(array("user_id"=>$user_id,"status"=>0,"sk_status"=>0))->orderBy('id', 'desc')->get();
            foreach ($order_info as &$v) {
                $v['name'] = $this->getname($v['erweima_id']);
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
            }
            ajaxReturn($order_info,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 接单订单列表
     */
    public function orderjd_list(Request $request) {
        if($request->isMethod('post')) {
            $user_id =$this->uid;//用户id
            $lastid = $request->input('lastid');
            if($lastid){
                $where =[['user_id',$user_id],['id','<',$lastid]];
            }else{
                $where =array('user_id'=>$user_id);
            }
            $bg_time = strtotime(date('Y-m-d'));
            $recmoney =  Orderrecord::where([['user_id',$user_id],['creatime','>=',$bg_time]])->sum('tradeMoney');
            $sucmoney =  Orderrecord::where([['user_id',$user_id],['status',1],['creatime','>=',$bg_time]])->sum('tradeMoney');
            $list=Orderrecord::where($where)->limit(10)->orderBy('id', 'desc')->get();
            foreach ($list as $k =>&$v) {
                $v['tradeMoney']= $v['tradeMoney']/100;
                $v['payMoney']= $v['payMoney']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['name'] = $this->getname($v['erweima_id']);
            }
            $data = array(
                'recmoney'=>$recmoney/100,
                'sucmoney'=>$sucmoney/100,
                'list'=>$list
            );
            ajaxReturn($data,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }

    /**获取订单详细信息
     * freezenum 冻结金额
     * unfreezenum 解冻金额
     * deductnum 扣除金额
     * brokerage 佣金
     * erweima 二维码
     * @param Request $request
     */
    public function orderjd_listinfo(Request $request) {
        if($request->isMethod('post')) {
            $user_id =$this->uid;//用户id
            $order_sn = $request->input('order_sn');
            $orderinfo = Orderrecord::where([['user_id',$user_id],['order_sn',$order_sn]])->select('erweima_id','dj_status')->first();
            if(!$orderinfo){
                ajaxReturn(null,'无此订单信息!',1);
            }
            $counttable = Accountlog::getcounttable($order_sn);
            $freezenum=  - $counttable->where([['user_id',$user_id],['order_sn',$order_sn],['status',3]])->value('score') /100;
            if($orderinfo['dj_status']==1){
                $unfreezenum =  $counttable->where([['user_id',$user_id],['order_sn',$order_sn],['status',4]])->value('score') /100;
                $deductnum =  '未扣除';
            }elseif($orderinfo['dj_status']==2){
                $unfreezenum =  $counttable->where([['user_id',$user_id],['order_sn',$order_sn],['status',4]])->value('score') /100;
                $deductnum =  - $counttable->where([['user_id',$user_id],['order_sn',$order_sn],['status',2]])->value('score') /100;
            }else{
                $unfreezenum =  '未解冻';
                $deductnum =  '未扣除';
            }
            if(Rebate::where([['user_id',$user_id],['order_sn',$order_sn],['user_is_fy',2]])->first()){
                $brokerage = $counttable->where([['user_id',$user_id],['order_sn',$order_sn],['status',5]])->value('score') /100;
            }else{
                $brokerage ='未返佣';
            }
            $erweima =Erweima::where([['user_id',$user_id],['id',$orderinfo['erweima_id']]])->value('erweima');
            $data = array(
                'freezenum'=>$freezenum,//冻结金额
                'unfreezenum'=>$unfreezenum,//解冻金额
                'deductnum'=>$deductnum,//扣除金额
                'brokerage'=>$brokerage,//佣金
                'erweima'=>$erweima//二维码
            );
            ajaxReturn($data,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 码商手动收款
     */
    public function savesk_status(Request $request) {
        if($request->isMethod('post')) {
            $user_id =$this->uid;//用户id
            $order_sn =$_POST['order_sn'];
            $skmoney=(int)$_POST['skmoney'];
            $order_info=Orderrecord::where(array('user_id'=>$user_id,'order_sn'=>$order_sn,'sk_status'=>0))->first();
            if(empty($order_info)) {
                ajaxReturn(null,'订单已处理!',0);
            }
            if(Orderrecord::where(array('user_id'=>$user_id,'order_sn'=>$order_sn,'sk_status'=>2))->first()) {
                ajaxReturn(null,'系统回调成功,您已收款成功,请刷新当前页面!',0);
            }
            if(Orderrecord::where(array('user_id'=>$user_id,'order_sn'=>$order_sn,'sk_status'=>1))->first()) {
                ajaxReturn(null,'请勿重复点击!',0);
            }
            $order = Order::getordersntable($order_sn);
            //更改码商收款金额
            $orderstatus = $order->where(array('user_id'=>$user_id,'order_sn'=>$order_sn))->update(array('sk_money'=>$skmoney*100,'sk_status'=>1));
            Orderrecord::where(array('user_id'=>$user_id,'order_sn'=>$order_sn))->update(array('sk_money'=>$skmoney*100,'sk_status'=>1));
            //  判断用户输入金额是否与支付金额一致
            if ( $order_info['tradeMoney'] != $skmoney*100) {
                ajaxReturn(null,'交易金额不匹配,已提交客服!',0);
            }
            if($orderstatus) {
                // 未超时
                if (time() - 3600 <$order_info['creatime'] ) {
                    $this->budan($order_info,$order_sn);
                } else {
                    //超时
                    $this->csbudan($order_info,$order_sn);
                }
                $this->insertrebatte($user_id,$order_info['business_code'],$order_sn,$skmoney * 100,$order_info['payType']);
                ajaxReturn(null,'手动收款成功!',1);
            } else {
                ajaxReturn(null,'手动收款失败!',0);
            }
        } else {
            ajaxReturn(null,'请求数据异常!',0);
        }
    }
    private function budan($order_sn_info,$order_sn) {
        /**确认收款 资金解冻 资金扣除 修改订单状态
         * @param $order_sn_info 订单信息
         * @param $order_sn 订单号
         */
        $tradeMoney =$order_sn_info['tradeMoney'];
        $data['score']=$order_sn_info['tradeMoney'];
        $data['user_id'] = $order_sn_info['user_id'];
        $data['status']=4;
        $data['erweima_id']=$order_sn_info['erweima_id'];
        $data['business_code']=$order_sn_info['business_code'];
        $data['order_sn']=$order_sn_info['order_sn'];
        $data['remark']="手动资金解冻";
        $data['creatime']=time();
        $account = Accountlog::getcounttable($order_sn);
        $account->insert($data);
        $info['score']=-$order_sn_info['tradeMoney'];
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['order_sn']=$order_sn_info['order_sn'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        $account->insert($info);
        Userscount::where('user_id',$order_sn_info['user_id'])->decrement('freeze_money',$tradeMoney,['tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
        $order = Order::getordersntable($order_sn);
        // 修改订单状态
        $order->where(array("order_sn"=>$order_sn))->update(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
        Orderrecord::where(array("order_sn"=>$order_sn))->update(array("status"=>1,"dj_status"=>2,"pay_time"=>time()));
//        $this->sfpushfirst($order_sn_info['order_sn']);
    }
    private function csbudan($order_sn_info,$order_sn) {
        /**确认收款超时 资金扣除
         * @param $order_sn_info 订单信息
         * @param $order_sn 订单号
         */
        $tradeMoney =$order_sn_info['tradeMoney'];
        $info['score']=-$tradeMoney;
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['order_sn']=$order_sn_info['order_sn'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        $account = Accountlog::getcounttable($order_sn);
        $account->insert($info);
        Userscount::where('user_id',$order_sn_info['user_id'])->decrement('balance',$tradeMoney,['tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
        $order = Order::getordersntable($order_sn);
        // 修改订单状态
        $order->where(array("order_sn"=>$order_sn))->update(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));
        Orderrecord::where(array("order_sn"=>$order_sn))->update(array("status"=>1,"dj_status"=>2,"pay_time"=>time()));
//        $this->sfpushfirst($order_sn_info['order_sn']);
    }

    /**返佣数据插入
     * @param $user_id
     * @param $bussiness_code
     * @param $order_sn
     * @param $skmoney
     * @param $payType
     */
    private function insertrebatte($user_id,$bussiness_code,$order_sn,$skmoney,$payType){
        $data=array(
            'user_id'=>$user_id,
            'business_code'=>$bussiness_code,
            'order_sn'=>$order_sn,
            'tradeMoney'=>$skmoney,
            'payType'=>$payType,
            'creatime'=>time()
        );
        //插入佣金表
        Rebate::insert($data);
    }
    /**
     *第一次 异步回调
     */
    private function sfpushfirst($order_sn) {
        $key = "36cae679f8cb296d69be4f27bd8cc3d6";
        if($key == '36cae679f8cb296d69be4f27bd8cc3d6') {
            $orderinfo=Order::where(array("order_sn"=>$order_sn))->get();
            if($orderinfo) {
                foreach ($orderinfo as $k=>$v) {
                    $url=$v['notifyUrl'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$v['payMoney'],
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $businessinfo=Business::where(array("business_code"=>$v['business_code']))->first();
                    if(empty($businessinfo)) {
                        ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res=$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',$orderinfo.PHP_EOL,FILE_APPEND);
                    if($res == 'success') {
                        file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        Order::where(array('id'=>$v['id']))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                        ajaxReturn('','回调成功!');
                    } else {
                        file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        Order::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                        //$Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                        ajaxReturn('','回调成功!第三方返回失败');
                    }
                }
            } else {
                ajaxReturn('','订单不存在',0);
            }
        } else {
            ajaxReturn('','蛇皮让你蛇皮',0);
        }
    }
    /**签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSignK($Obj,$key) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String =$this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        // $this->writeLog($String);
        //签名步骤二：在string后加入KEY
        $String = $String."&accessKey=".$key;
        //echo "【string2】".$String."</br>";
        //echo $String;
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    private function https_post_kfs($url,$data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
    /**字典排序 & 拼接
     * @param $paraMap
     * @param $urlencode
     * @return bool|string
     */
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    /**
     * @param $erweima_id
     * @return mixed
     * 获取二维码名称
     */
    private function getname($erweima_id) {
        $name=Erweima::where(array('id'=>$erweima_id))->value('name');
        return $name;
    }
}