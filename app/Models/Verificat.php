<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/3
 * Time: 9:14
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Request;
class Verificat extends Model {
    protected  $table = 'verificat';
    public $timestamps = false;

    /**云通信发送短信
     * @param $mobile
     * @param $code
     * @param $ip
     * @return bool|int|mixed|string
     */
    public static function dxbsend($mobile,$code,$ip){
        $status=Redis::get('sendsms_lock_'.$ip);
        if($status==2){
            return 123;
        }

        Redis::setex('sendsms_lock_'.$ip,60,2);
        $data['username']='fjnphy';
        $data["password"] = md5(md5("Uj41oPwQ").time());//密码
        $data["mobile"] = $mobile;//手机号
        $data["content"] = '【EPP】您的验证码为'.$code.'，在5分钟内有效。';
        $data["tKey"]=time();
        $url = 'http://api.mix2.zthysms.com/v2/sendSms';

        $data=json_encode($data);
        //   print_r($data);
        $res = Verificat::https_post_kf($url,$data);

        $res=json_decode($res,true);

        if ($res['code'] == 200){
            $res=0;
        }else{
            $res=false;
        }
        return $res;
    }
    public static function dxbsends($mobile,$code){

        $ip=getip();
        $status=Cac()->get('sendsms_lock_'.$ip);
        if($status==1){
            return 123;
        }
        Cac()->set('sendsms_lock_'.$ip,1,60);

        //http://api.smsbao.com/sms?u=USERNAME&p=PASSWORD&m=PHONE&c=CONTENT
        //return "0";
        $username='q17152437247';
        $password=md5('qwer1234');


        $content=urlencode('【天马】您的验证码为'.$code.'，在5分钟内有效。');
        $host="http://api.smsbao.com/sms?u=".$username."&p=".$password."&m=".$mobile."&c=".$content;
        $res=file_get_contents($host);
        return $res;
        //return false;
    }
    /**存储手机验证码发送记录
     * @param $code
     * @param $mobile
     * @param $type
     * @param $ip
     * @param $status
     * @param $sendmsg
     */
    public static function insertsendcode($code,$mobile,$type,$ip,$status,$sendmsg) {
        $data=array(
            'code'=>$code,
            'phone'=>$mobile,
            'type'=>$type,
            'sendip'=>$ip,
            'status'=>$status,
            'sendmsg'=>$sendmsg,
            'creatime'=>time()
        );
        Verificat::insert($data);
    }

    /**发送请求
     * @param $url
     * @param $data
     * @return mixed|string
     */
    private static function https_post_kf($url, $data)
    {
        $headers = array(
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }

}