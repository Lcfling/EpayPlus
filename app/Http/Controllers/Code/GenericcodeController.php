<?php
namespace App\Http\Controllers\Code;
use App\Models\Accountlog;
use App\Models\Erweima;
use App\Models\Jhmoney;
use App\Models\Users;
use App\Models\Userscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use \GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Storage;
class GenericcodeController extends CommonController {

    /**码商上码
     * @param Request $request
     */
    public function shangma(Request $request){
        if ($request->isMethod('POST')) {
            $user_id = $this->uid;
            $type = (int)$_POST['type']; //类型
            $username = htmlformat($_POST['name']); //姓名
            $min = (int)$_POST['min'];   // 最小值
            $max = (int)$_POST['max'];   //最大值

            // 查看用户费率
            $userinfo = Users::where(array("user_id" => $user_id))->first();
            if (!$userinfo["rate"] > 0) {
                ajaxReturn("", "请联系上级设置费率" . $this->uid, 0);
            }

            //限定格式为jpg,jpeg,png
            $fileTypes = ['jpeg', 'jpg','png'];
            if ($request->hasFile('uploadfile')) {
                $uploadfile =$request->file('uploadfile');

                if (is_array($request->file('uploadfile'))) {
                    foreach ($request->file('uploadfile') as $file) {
                        if ($file->isValid()) { //判断文件上传是否有效
                            $FileType = $file->getClientOriginalExtension(); //获取文件后缀
                            file_put_contents('./FileType.txt',"~~~~~~~~~~~~~~~图片格式~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                            file_put_contents('./FileType.txt',$FileType.PHP_EOL,FILE_APPEND);
                            if (!in_array($FileType, $fileTypes)) {
                                ajaxReturn("","图片格式为jpg,png,jpeg",0);
                            }
                            $file_relative_path = '/erweima/'.date('Y-m-d');
                            $file_path = public_path($file_relative_path);
                            if (!is_dir($file_path)){
                                mkdir($file_path);
                            }
                            $FilePath = $file->getRealPath(); //获取文件临时存放位置
                            $FileName = $file_relative_path.'/'.date('YmdHis') . uniqid() . '.' . $FileType; //定义文件名
                            Storage::disk('imgupload')->put($FileName, file_get_contents($FilePath)); //存储文件
                        }
                        $data =array(
                            'user_id'=>$user_id,
                            'erweima'=>$FileName,
                            'status'=>0,
                            'type'=>$type,
                            'name'=>$username,
                            'max'=>$max,
                            'min'=>$min,
                            'code_status'=>0,
                            'creatime'=>time()
                        );
                        $id = Erweima::insertGetId($data);
                        if ($id) {
                            // 二维码存入用户缓冲
                            Redis::rPush('erweimas' . $type . $user_id, $id);
                            //二维码信息存入用户缓存
                            Redis::set("erweimainfo_".$id,json_encode($data));
                            ajaxReturn("", "成功");
                        } else {
                            ajaxReturn("", "图片存储失败!", 0);
                        }
                    }
                } else {
                    if ($uploadfile->isValid()) { //判断文件上传是否有效
                        $FileType = $uploadfile->getClientOriginalExtension(); //获取文件后缀
                        if (!in_array($FileType, $fileTypes)) {
                            ajaxReturn("","图片格式为jpg,png,jpeg",0);
                        }
                        $file_relative_path = '/erweima/'.date('Y-m-d');
                        $file_path = public_path($file_relative_path);
                        if (!is_dir($file_path)){
                            mkdir($file_path);
                        }
                        $FilePath = $uploadfile->getRealPath(); //获取文件临时存放位置
                        $FileName = $file_relative_path.'/'.date('YmdHis') . uniqid() . '.' . $FileType; //定义文件名
                        Storage::disk('imgupload')->put($FileName, file_get_contents($FilePath)); //存储文件
                    }
                    $data =array(
                        'user_id'=>$user_id,
                        'erweima'=>$FileName,
                        'status'=>0,
                        'type'=>$type,
                        'name'=>$username,
                        'max'=>$max,
                        'min'=>$min,
                        'code_status'=>0,
                        'creatime'=>time()
                    );
                    $id = Erweima::insertGetId($data);
                    if ($id) {
                        // 二维码存入用户缓冲
                        Redis::rPush('erweimas' . $type . $user_id, $id);
                        //二维码信息存入用户缓存
                        Redis::set("erweimainfo_".$id,json_encode($data));
                        ajaxReturn("", "成功");
                    } else {
                        ajaxReturn("", "图片存储失败!", 0);
                    }
                }
            } else {
                ajaxReturn("","未上传图片!",0);
            }
        }else {
            ajaxReturn('','请求数据异常!',0);
        }
    }


    /**
     * 账号激活
     */
    public function activate() {
        $user_id=$this->uid;
        $userinfo = $this->member;
        //查询激活信息
        $jhmoney=Jhmoney::first();
        $money = $jhmoney['jhmoney'];
        if ($userinfo['jh_status'] == 1) {
            ajaxReturn($userinfo,"账号已激活!",0);
        }
        DB::beginTransaction();
        // 查看用户积分余额
        $balance =DB::table('users_count')->where('user_id',$user_id)->lockForUpdate()->value('balance');
        if ($balance<$money) {
            DB::rollBack();
            ajaxReturn($balance,"账户余额不足!",0);
        }
        $countstatus = DB::table('users_count')->where('user_id',$user_id)->decrement('balance',$money,['active_money'=>DB::raw("active_money + $money")]);
        if(!$countstatus){
            DB::rollBack();
            ajaxReturn($balance,"更改账户失败!",0);
        }
        $daytable =Accountlog::getdaytable();
        // 积分扣除
        $accountstatus=$daytable->insert(
            array(
                'user_id'=>$user_id,
                'score'=>-$money,
                'status'=>7,
                'remark'=>'账户激活',
                'creatime'=>time()
            )
        );
        if ($accountstatus) {
            //更改账户状态
            $jhstatus = Users::where(array("user_id"=>$user_id))->update(['jh_status' => 1]);
            if($jhstatus){
                DB::commit();
            }else{
                DB::rollBack();
                ajaxReturn($balance,"更改激活状态失败!",0);
            }
            Jhmoney::jihuofy($userinfo['pid'],1);
            ajaxReturn($accountstatus,"激活成功!");
        } else {
            DB::rollBack();
            ajaxReturn($balance,"激活失败!",0);
        }
    }
    /**
     * 激活佣金金额
     */
    public function jhmoney() {
        $jhmoney=Jhmoney::first();
        ajaxReturn($jhmoney,"激活佣金!");
    }
    /**
     * 二维码开关
     */
    public function codekg() {
        $user_id=$this->uid;
        $erweima_id=(int)$_POST['erweima_id'];
        //查询二维码信息
        $erweimainfo=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->first();
        if ($erweimainfo['code_status']==0) {
            $code_status=1;
            $msg="关闭二维码接单";
            //移除二维码队列
            Redis::lRem('erweimas'.$erweimainfo['type'].$user_id,0,$erweima_id);
        } else {
            $code_status=0;
            $msg="开启二维码接单";
            // 二维码存入用户缓冲
            Redis::rPush('erweimas'.$erweimainfo['type'].$user_id,$erweima_id);
        }
        // 修改二维码状态
        $savestatus=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id))->update(array("code_status"=>$code_status));
        ajaxReturn($savestatus,$msg);
    }
}