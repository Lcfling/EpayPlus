<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/10/31
 * Time: 16:44
 */
namespace App\Http\Controllers\Code;

use App\Models\Czrecord;
use App\Models\Order;
use App\Models\Userscount;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GatewayClient\Gateway;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer;



class IndexController extends Controller
{
    public function welcome(Request $request){
//        for($i=0;$i<2000;$i++){
//            $res =DB::table('users')->insert(array('second_pwd'=>1,'is_over'=>12,'account'=>123,'password'=>123456,'reg_ip'=>'0.0.0.0'));
//        }
//        print_r($res);
//        if($request->isMethod('post')){
//            $res =$request->input();
//        }
//        $this->ajaxReturn('123');
//        $userinfo =DB::table('users')->where(array('user_id'=>1))->first();
//        print_r(get_object_vars($userinfo));
//        print_r($this->uid);
//        Redis::set('aaa',123);

//        print_r(geoip('115.54.175.76')->toArray());
//        print_r(Redis::get('aaa'));
//
//        for ($i=0;$i<10000;$i++){
//            $res[] =getrequestId('1','300013','14');
//        }
//        $ress = FetchRepeatMemberInArray($res);
//        print_r($res);
//        print_r($ress);

//        $data = array(
//            'user_id'=>1,
//            'order_no'=>getrequestId('1','300013','14'),
//            'money'=>10000,
//            'creatime'=>time(),
//            'status'=>1
//        );
//        $data = array(
//            'user_id'=>1,
//            'name'=>111,
//            'score'=>10000,
//            'czimg'=>getrequestId('1','300013','14'),
//            'creatime'=>time(),
//            'status'=>1
//        );
//        for ($i=0;$i<10000;$i++){
//            Czrecord::insert($data);
//        }
//        $type = 2;
//        $user_id=1;
//        $id =2;
//        Redis::rPush('erweimas'.$type.$user_id,$id);
//        $google2fa = new Google2FA();
//        $secretKey=$google2fa->generateSecretKey();
//        $qrCodeUrl = $google2fa->getQRCodeUrl(
//            "EPayPlus",//名称后台获取
//            13632470525,
//            $secretKey
//        );
//       $code = "<img src='{$qrCodeUrl}'>";
//        echo $code;
//        print_r($secretKey);
//        $tradeMoney =10000;
//        Userscount::where('user_id',1)->increment('balance',$tradeMoney,['freeze_money'=>DB::raw("freeze_money - $tradeMoney"),'tol_sore'=>DB::raw("tol_sore + $tradeMoney")]);
//        header("content-type:text/html;charset=utf-8");
//        $str = "'123456'     ";
//         // 转换双引号和单引号
//        echo  preg_replace('/\'/', '', str_replace(" ",'',htmlspecialchars($str)));
//        print_r($balance = Userscount::onWriteConnection()->where('user_id',1)->value('balance'));
//        $tradeMoney =100;
//        Userscount::onWriteConnection()->where('user_id',1)->decrement('balance',$tradeMoney,['freeze_money'=>DB::raw("freeze_money + $tradeMoney")]);

//        try {
//            $cmd = Userscount::onWriteConnection()->where('user_id',1)->decrement('balance','abc');
//            $cmd->execute();
//        } catch (Exception $e) {
//            print $e->getMessage();
//            exit();
//        }
//        $res = Order::getordersntable(getrequestId());
//        print_r($res);
//        Redis::rPush('erweimas00000',12);
//        Redis::rPush('erweimas00000',10);
//        Redis::rPush('erweimas00000',9);
//        Redis::rPush('erweimas00000',8);
//        Redis::rPush('erweimas00000',11);
//        $list = Redis::lrange('erweimas00000',0,-1);
//        print_r($list);
//        Redis::lRem('erweimas00000',0,11);
//        Redis::rPush('erweimas00000',11);
//        $list = Redis::lrange('erweimas00000',0,-1);
//        print_r($list);
//        $ordertatle = Order::getordertable();
//        $weeksuf = computeWeek(time(),false) - 1;
//        $ordertatlepre = Order::getordertable($weeksuf);
//        $first  =$ordertatlepre->where(array("user_id"=>1,"status"=>0,"sk_status"=>0));
//        $res = $ordertatle->where(array("user_id"=>37,"status"=>0,"sk_status"=>0))->union($first)->orderBy('creatime', 'desc')->get()->toArray();
//        print_r($res);
        print_r(Redis::decr('order_qd_1'));
    }


    /**
     * 检测更新
     */

    public function update(){
        $v=$_POST['currentversion'];
        $data['force']='1';
        $data['detail']='版本更新信息';
        $data['force']='2';
        ajaxReturn($data,'版本更新',1);
        $data['url']='http://download.fir.im/apps/5dad930cf9454818b513fdcd/install?download_token=2d45ab6fe012f8d8bf70d9049bfee334';

        if($v=="1.0.6"){
            $data['force']='2';
            ajaxReturn($data,'最新版本',1);
        }else{
            $data['force']='1';
            ajaxReturn($data,'版本更新',1);
        }
    }

    /**
     * 测试支付调取
     */
    public function index() {
//        print_r($_POST);exit();
        $codeType =$_POST['codeType'];
        $data["payType"] = $_POST['payType'];
        //支付方式  1微信  2支付宝
        $data["codeType"] = $codeType;
        //二维码类型  1 固码  2 通用码
        $data["out_order_sn"] = $_POST['out_order_sn'];
        //订单号
        $data["tradeMoney"] = $_POST['money'];
        $data["notifyUrl"] = "http://".$_SERVER['HTTP_HOST']."/code/orderym/notifyUrl";
        //回调
        $key = $_POST['accessKey'];
        $data["sign"] = $this->getSign($data,$key);
        //签名
        $data["business_code"] = $_POST['business_code'];
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/code/orderym/kuaifupay';
        $res = $this->https_post_kf($url,$data);
        print_r($res);
        exit();
    }

    public function notifyUrl() {
        $retrun_datas =$_POST;
        $retrun_sign=$retrun_datas['sign'];
        //签名值
        unset($retrun_datas['sign']);
        $key = '8551e0027ff3a8de9662eb3b8a16c23e';
        $sign =$this->getSign($retrun_datas,$key);
        if($retrun_sign==$sign) {
            echo "success";
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true).PHP_EOL,FILE_APPEND);
        } else {
            echo "fail";
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true).PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt','sign-'.$sign.PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt','retrun_sign-'.$retrun_sign.PHP_EOL,FILE_APPEND);
        }
    }
    private function getSign($Obj,$key) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //        echo '【string1】' . $String . '</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&accessKey=" . $key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    private function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    private function https_post_kf($url, $data) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
}


