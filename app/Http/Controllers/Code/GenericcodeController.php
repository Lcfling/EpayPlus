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
                $this->ajaxReturn("", "请联系上级设置费率" . $this->uid, 0);
            }

            //限定格式为jpg,jpeg,png
            $fileTypes = ['jpeg', 'jpg','png'];
            if ($request->hasFile('uploadfile')) {
                $uploadfile =$request->file('uploadfile');

                if (is_array($request->file('uploadfile'))) {
                    foreach ($request->file('uploadfile') as $file) {
                        if ($file->isValid()) { //判断文件上传是否有效
                            $FileType = $file->getClientOriginalExtension(); //获取文件后缀
                            file_put_contents('./FileType.txt',$FileType.PHP_EOL,FILE_APPEND);
                            if (!in_array($FileType, $fileTypes)) {
                                ajaxReturn("","图片格式为jpg,png,jpeg",0);
                            }
                            $FilePath = $file->getRealPath(); //获取文件临时存放位置
                            file_put_contents('./FileType.txt',$FilePath.PHP_EOL,FILE_APPEND);
                            $FileName = date('Y-m-d') . uniqid() . '.' . $FileType; //定义文件名
                            file_put_contents('./FileType.txt',$FileName.PHP_EOL,FILE_APPEND);
                            Storage::disk('erweima')->put($FileName, file_get_contents($FilePath)); //存储文件
                        }
                        $data =array(
                            'user_id'=>$user_id,
                            'erweima'=>"/erweima/" . $FileName,
                            'status'=>0,
                            'type'=>$type,
                            'name'=>$username,
                            'max'=>$max,
                            'min'=>$min,
                            'creatime'=>time()
                        );
                        $id = Erweima::insertGetId($data);
                        if ($id) {
                            // 二维码存入用户缓冲
                            Redis::rPush('erweimas' . $type . $user_id, $id);
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
                        $FilePath = $uploadfile->getRealPath(); //获取文件临时存放位置

                        $FileName = date('Y-m-d') . uniqid() . '.' . $FileType; //定义文件名

                        Storage::disk('erweima')->put($FileName, file_get_contents($FilePath)); //存储文件
                    }
                    $data =array(
                        'user_id'=>$user_id,
                        'erweima'=>"/erweima/" . $FileName,
                        'status'=>0,
                        'type'=>$type,
                        'name'=>$username,
                        'max'=>$max,
                        'min'=>$min,
                        'creatime'=>time()
                    );
                    $id = Erweima::insertGetId($data);
                    if ($id) {
                        // 二维码存入用户缓冲
                        Redis::rPush('erweimas' . $type . $user_id, $id);
                        ajaxReturn("", "上传成功");
                    } else {
                        ajaxReturn("", "上传失败!", 0);
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

        if ($userinfo['jh_status'] == 1) {
            ajaxReturn($userinfo,"账号已激活!",0);
        }
        DB::beginTransaction();
        // 查看用户积分余额
        $balance =DB::table('users_count')->where('user_id',$user_id)->value('balance');
        if ($balance<$jhmoney['jhmoney']) {
            ajaxReturn($balance,"账户余额不足!",0);
        }
        DB::table('users_count')->where('user_id',$user_id)->decrement('balance',$jhmoney['jhmoney']);
        DB::commit();
        $daytable =Accountlog::getdaytable();
        // 积分扣除
        $status=$daytable->insert(
            array(
                'user_id'=>$user_id,
                'score'=>-$jhmoney['jhmoney'],
                'status'=>7,
                'remark'=>'账户激活',
                'creatime'=>time()
            )
        );
        if ($status) {
            //更改账户状态
            Users::where(array("user_id"=>$user_id))->update(['jh_status' => 1]);
            Jhmoney::jihuofy($userinfo['pid'],1);
            ajaxReturn($status,"激活成功!");
        } else {
            ajaxReturn($status,"激活失败!",0);
        }
    }
    /**
     * 激活佣金金额
     */
    public function jhmoney() {
        $jhmoney=Jhmoney::first();
        $this->ajaxReturn($jhmoney,"激活佣金!");
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
            Redis::lRem('erweimas'.$erweimainfo['type'].$user_id,$erweima_id,0);
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