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
class GenericcodeController extends CommonController {


// 码商上码
    public function shangma(){

        $user_id=$this->uid;
        $type=(int)$_POST['type']; //类型
        $username=htmlspecialchars($_POST['name']); //姓名
        $home=htmlspecialchars($_POST['home']);   //地址
        $min=(int)$_POST['min'];   // 最小值
        $max=(int)$_POST['max'];   //最大值



        // 查看用户费率
        $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        if ( ! $userinfo["rate"]>0){
            $this->ajaxReturn("","请联系上级设置费率".$this->uid,0);
        }




        foreach ($_FILES["uploadfile"]["name"] as $k=>$v){
            if ( !file_exists("./erweima/" . $_FILES["uploadfile"]["name"][$k]))
            {

                $rand=rand(10000,99999);
                $fileName=$_FILES['uploadfile']['name'][$k];//得到上传文件的名字
                $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
                //$ext=pathinfo($fileName);
                switch ($_FILES['uploadfile']['type'][$k]){
                    case "image/png":
                        $named="png";
                        break;
                    case "image/jpeg":
                        $named="jpeg";
                        break;
                    case "image/jpg":
                        $named="jpg";
                        break;
                    case "image/*":
                        $named="jpg";
                        break;
                    default:
                        $this->ajaxReturn("","失败",0);
                        return;
                }

                $date=date('Ymdhis');//得到当前时间,如;20070705163148
                $newPath=$date.$rand.'.'.$named;//得到一个新的文件为'20070705163148.jpg',即新的路径

                move_uploaded_file($_FILES["uploadfile"]["tmp_name"][$k], "../img/erweima/" .$newPath);
            }


            $data['user_id']=$user_id;
            $data['erweima'] ="/erweima/" .$newPath;
            $data['status']=0;
            $data['type']=$type;
            $data['name']=$username;
            $data['home']=$home;
            $data['max']=$max;
            $data['min']=$min;
            $data['creatime']=time();
            $id=D("erweima_generic")->add($data);
            if($id){

                // 二维码存入用户缓冲
                Cac()->rPush('erweimas'.$type.$user_id,$id);
                $this->ajaxReturn("","成功");
            }else{
                $this->ajaxReturn("","失败2",0);
            }


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
        // 查看用户积分
        $balance = Userscount::where(array('user_id'=>$user_id))->value('balance');
        if ($balance<$jhmoney['jhmoney']) {
            ajaxReturn($balance,"账户余额不足!",0);
        }
        if ($userinfo['jh_status'] == 1) {
            ajaxReturn($userinfo,"账号已激活!",0);
        }
        // 积分扣除
        $status=Accountlog::insert(
            array(
                'user_id'=>$user_id,
                'score'=>-$jhmoney->jhmoney,
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