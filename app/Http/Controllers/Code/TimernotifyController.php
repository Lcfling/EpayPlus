<?php/** * Created by PhpStorm. * User: LK * Date: 2019/5/15 * Time: 11:47 */namespace App\Http\Controllers\Code;use App\Models\Accountlog;use App\Models\Agentfee;use App\Models\Business;use App\Models\Businessbillflow;use App\Models\Order;use App\Models\Orderrecord;use App\Models\Rebate;use App\Models\Users;use Illuminate\Http\Request;use App\Http\Controllers\Controller;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Redis;class TimernotifyController extends Controller {    /**     *第一次 异步回调     */    public function sfpushfirst(){        $key = $_GET['key'];        if($key == '9a1b1f272be8c08c9ef05f601f9dde5d'){            $orderrecordinfo =Orderrecord::where(array('status'=>1,'callback_status'=>0,'callback_num'=>0))->orderBy('pay_time','asc')->limit(10)->get()->toArray();            if($orderrecordinfo){                foreach ($orderrecordinfo as $k=>$v){                    $submeter_name = $v['submeter_name'];                    $order =new Order();                    $Order =$order->setTable($submeter_name);                    $order_sn =$v['order_sn'];                    $orderinfo=$Order->where(array('order_sn'=>$order_sn,'status'=>1))->first();                    $url=$orderinfo['notifyUrl'];                    $data=array(                        'order_sn'=>$order_sn,                        'business_sn'=>$orderinfo['business_sn'],                        'tradeMoney'=>$orderinfo['tradeMoney'],                        'pay_time'=>$orderinfo['pay_time'],                        'status'=>$orderinfo['status']                    );                    $where['business_code']=$orderinfo['business_code'];                    $businessinfo=Business::where($where)->first();                    if(empty($businessinfo)){                        ajaxReturn('error40003','商户未启用!',0);                    }                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);                    $res =$this->https_post_kfs($url,$data);                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                    file_put_contents('./notifyUrl.txt',print_r($orderinfo,true).PHP_EOL,FILE_APPEND);                    $time =time();                    if($res == 'success'){                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>$time));                    }else{                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0))->update(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>$time));                    }                }            }        }else{            $data['status']= 0;            $data['msg']='蛇皮让你蛇皮!';            echo json_encode($data);        }    }    /**     * 第二次异步回调     */    public function sfpushsecond(){        $key = $_GET['key'];        if($key == 'c04c96f89ade5c6e88deaf5a8b351c35'){            $orderrecordinfo =Orderrecord::where(array('status'=>1,'callback_status'=>0,'callback_num'=>1))->orderBy('pay_time','asc')->limit(10)->get()->toArray();            if($orderrecordinfo){                foreach ($orderrecordinfo as $k=>$v){                    $submeter_name = $v['submeter_name'];                    $order =new Order();                    $Order =$order->setTable($submeter_name);                    $order_sn =$v['order_sn'];                    $orderinfo=Business::where(array('order_sn'=>$order_sn,'status'=>1))->first();                    $url=$orderinfo['notifyUrl'];                    $data=array(                        'order_sn'=>$order_sn,                        'business_sn'=>$orderinfo['business_sn'],                        'tradeMoney'=>$orderinfo['tradeMoney'],                        'pay_time'=>$orderinfo['pay_time'],                        'status'=>$orderinfo['status']                    );                    $business = db('business');                    $where['business_code']=$orderinfo['business_code'];                    $businessinfo=$business->where($where)->find();                    if(empty($businessinfo)){                        ajaxReturn('error40003','商户未启用!',0);                    }                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);                    $res =$this->https_post_kfs($url,$data);                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                    file_put_contents('./notifyUrl.txt',print_r($orderinfo,true).PHP_EOL,FILE_APPEND);                    $time =time();                    if($res == 'success'){                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->update(array('callback_status'=>1,'callback_num'=>2,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->update(array('callback_status'=>1,'callback_num'=>2,'callback_time'=>$time));                    }else{                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->update(array('callback_status'=>0,'callback_num'=>2,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->update(array('callback_status'=>0,'callback_num'=>2,'callback_time'=>$time));                    }                }            }        }else{            $data['status']= 0;            $data['msg']='蛇皮让你蛇皮!';            echo json_encode($data);        }    }    /**     * 第三次异步回调     */    public function sfpushthird(){        $key = $_GET['key'];        if($key == '3592f1c48196014b9161bc52aeefb45e'){            $orderrecordinfo =Orderrecord::where(array('status'=>1,'callback_status'=>0,'callback_num'=>2))->orderBy('pay_time','asc')->limit(10)->get()->toArray();            if($orderrecordinfo){                foreach ($orderrecordinfo as $k=>$v){                    $submeter_name = $v['submeter_name'];                    $order =new Order();                    $Order =$order->setTable($submeter_name);                    $order_sn =$v['order_sn'];                    $orderinfo=$Order->where(array('order_sn'=>$order_sn,'status'=>1))->first();                    $url=$orderinfo['notifyUrl'];                    $data=array(                        'order_sn'=>$order_sn,                        'business_sn'=>$orderinfo['business_sn'],                        'tradeMoney'=>$orderinfo['tradeMoney'],                        'pay_time'=>$orderinfo['pay_time'],                        'status'=>$orderinfo['status']                    );                    $where['business_code']=$orderinfo['business_code'];                    $businessinfo=Business::where($where)->first();                    if(empty($businessinfo)){                        ajaxReturn('error40003','商户未启用!',0);                    }                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);                    $res =$this->https_post_kfs($url,$data);                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                    file_put_contents('./notifyUrl.txt',print_r($orderinfo,true).PHP_EOL,FILE_APPEND);                    $time =time();                    if($res == 'success'){                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->update(array('callback_status'=>1,'callback_num'=>3,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->update(array('callback_status'=>1,'callback_num'=>3,'callback_time'=>$time));                    }else{                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);                        Orderrecord::where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->update(array('callback_status'=>0,'callback_num'=>3,'callback_time'=>$time));                        $Order->where(array('id'=>$orderinfo['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->update(array('callback_status'=>0,'callback_num'=>3,'callback_time'=>$time));                    }                }            }        }else{            $data['status']= 0;            $data['msg']='蛇皮让你蛇皮!';            echo json_encode($data);        }    }    /**     * 订单10分钟更改为过期     */    public function setstale() {        $bj_time = time() - 600;        if($orderinfo = Orderrecord::where([['status',0],['creatime','<',$bj_time]])->limit(10)->get()->toArray()) {            foreach ($orderinfo as $k=>$v){                Orderrecord::where(array('id'=>$v['id'],'status'=>0))->update(array('status'=>2));                $order =Order::getordersntable($v['order_sn']);                $order->where(array('status'=>0,'order_sn'=>$v['order_sn']))->update(array('status'=>2));            }        }else{            ajaxReturn('','暂无10分钟之前的未支付订单!',0);        }    }    /**     * 过期订单1个小时之后解冻并返回跑分 更改订单为订单取消     */    public function orderunfreeze() {        $code=time().rand(100000,999999);        //随机锁入队        Redis::rPush('orderunfreeze',$code);        //随机锁出队        $codes=Redis::LINDEX("orderunfreeze",0);        if ($code != $codes) {            die("滚");        }        $bj_time = time() - 900 ;        if($ordersinfo = Orderrecord::where([['status',2],['dj_status',0],['user_id','>',0],['creatime','<',$bj_time]])->limit(10)->get()->toArray()) {            foreach ($ordersinfo as $k=>$v) {                Orderrecord::where(array('id'=>$v['id'],'status'=>2,'dj_status'=>0))->update(array('status'=>3));                $Account_log = Accountlog::getcounttable($v['order_sn']);                $order =Order::getordersntable($v['order_sn']);                $order_sn = $v['order_sn'];                $user_id = $v['user_id'];                $payType = $v['payType'];                $orderinfo = $order->where(array('order_sn'=>$order_sn,'user_id'=>$user_id,'payType'=>$payType))->first();                $data=array(                    'user_id'=>$orderinfo['user_id'],                    'score'=>$orderinfo['tradeMoney'],                    'order_sn'=>$order_sn,                    'erweima_id'=>$orderinfo['erweima_id'],                    'business_code'=>$orderinfo['business_code'],                    'status'=>4,                    'payType'=>$orderinfo['payType'],                    'remark'=>'资金解冻',                    'creatime'=>time()                );                $Account_log->insert($data);                $order->where(array('order_sn'=>$order_sn,'user_id'=>$user_id,'payType'=>$payType))->update(array('dj_status'=>1));            }            Redis::del("orderunfreeze");        }else{            Redis::del("orderunfreeze");            ajaxReturn('','暂无过期订单!',0);        }    }    /**商户返佣     * @return mixed     * @throws \think\db\exception\DataNotFoundException     * @throws \think\db\exception\ModelNotFoundException     * @throws \think\exception\DbException     */    public function bussiness_fy(){        if(Rebate::where('sh_is_fy = 1')->first()){            ajaxReturn('','定时器执行中!',10001);        }else{            $fylist =Rebate::where('sh_is_fy = 0')->orderBy('creatime','asc')->limit(1)->first();            if($fylist){                Agentfee::where('id',$fylist['id'])->update(array('sh_is_fy'=>1));                $bussiness_code = $fylist['bussiness_code'];                $order_sn = $fylist['order_sn'];                $tradeMoney = $fylist['tradeMoney'];                $paycode = $fylist['paycode'];                $res = Agentfee::bussiness_fy($tradeMoney,$bussiness_code,$order_sn,$paycode);                Agentfee::where('id',$fylist['id'])->update(array('sh_is_fy'=>2,'shfytime'=>time()));                ajaxReturn($res,'执行成功!',1);            }else{                ajaxReturn('','未找到合伙人!',0);            }        }    }    /**码商返佣     * @return mixed     * @throws \think\db\exception\DataNotFoundException     * @throws \think\db\exception\ModelNotFoundException     * @throws \think\exception\DbException     */    public function user_fy(){        if(Rebate::where('user_is_fy = 1')->first()){            ajaxReturn('','定时器执行中!',10001);        }else{            $fylist =Rebate::where('user_is_fy = 0')->orderBy('creatime','asc')->limit(1)->first();            if($fylist){                Agentfee::where('id',$fylist['id'])->update(array('user_is_fy'=>1));                $bussiness_code = $fylist['bussiness_code'];                $order_sn = $fylist['order_sn'];                $tradeMoney = $fylist['tradeMoney'];                $paycode = $fylist['paycode'];                $user_id = $fylist['user_id'];                $res = Agentfee::user_fy($tradeMoney,$user_id,$bussiness_code,$order_sn,$paycode);                Agentfee::where('id',$fylist['id'])->update(array('user_is_fy'=>2,'userfytime'=>time()));                ajaxReturn($res,'执行成功!',1);            }else{                ajaxReturn('','未找到合伙人!',0);            }        }    }    /**发起请求     * @param $url     * @param $data     * @return mixed|string     */    private function https_post_kfs($url,$data) {        if (preg_match("/^(http:\/\/).*$/", $url)) {            $curl = curl_init();            curl_setopt($curl, CURLOPT_URL, $url);            curl_setopt($curl, CURLOPT_POST, 1);            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);            //        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));            $result = curl_exec($curl);            if (curl_errno($curl)) {                return 'Errno' . curl_error($curl);            }            curl_close($curl);            return $result;        } else if (preg_match("/^(https:\/\/).*$/", $url)) {            $curl = curl_init();            // 启动一个CURL会话            curl_setopt($curl, CURLOPT_URL, $url);            // 要访问的地址            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);            // 对认证证书来源的检查            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);            // 从证书中检查SSL加密算法是否存在            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);            // 模拟用户使用的浏览器            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);            // 使用自动跳转            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);            // 自动设置Referer            curl_setopt($curl, CURLOPT_POST, 1);            // 发送一个常规的Post请求            //            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);            // Post提交的数据包            curl_setopt($curl, CURLOPT_TIMEOUT, 30);            // 设置超时限制防止死循环            curl_setopt($curl, CURLOPT_HEADER, 0);            // 显示返回的Header区域内容            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);            // 获取的信息以文件流的形式返回            $result = curl_exec($curl);            // 执行操作            if (curl_errno($curl)) {                echo 'Errno' . curl_error($curl);                //捕抓异常            }            curl_close($curl);            // 关闭CURL会话            return $result;        }    }    /**签名     * @param $Obj     * @param $key     * @return string     */    private function getSignK($Obj,$key) {        foreach ($Obj as $k => $v) {            $Parameters[$k] = $v;        }        //签名步骤一：按字典序排序参数        ksort($Parameters);        $String =$this->formatBizQueryParaMap($Parameters, false);        //echo '【string1】'.$String.'</br>';        // $this->writeLog($String);        //签名步骤二：在string后加入KEY        $String = $String."&accessKey=".$key;        //echo "【string2】".$String."</br>";        //echo $String;        //签名步骤三：MD5加密        $String = md5($String);        //echo "【string3】 ".$String."</br>";        //签名步骤四：所有字符转为大写        $result_ = strtoupper($String);        //echo "【result】 ".$result_."</br>";        return $result_;    }    /**字典排序 & 拼接     * @param $paraMap     * @param $urlencode     * @return bool|string     */    function formatBizQueryParaMap($paraMap, $urlencode) {        $buff = "";        ksort($paraMap);        foreach ($paraMap as $k => $v) {            if($urlencode) {                $v = urlencode($v);            }            //$buff .= strtolower($k) . "=" . $v . "&";            $buff .= $k . "=" . $v . "&";        }        if (strlen($buff) > 0) {            $reqPar = substr($buff, 0, strlen($buff)-1);        }        return $reqPar;    }}?>