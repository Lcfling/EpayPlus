<?php
/**
 * Created by PhpStorm.
 * User: LK,JJ
 * Date: 2019/11/1
 * Time: 17:23
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class Users extends Model {
    protected  $table = 'users';
    public $timestamps = false;
    /**获取下面所有人
     * @param array $result
     * @return array
     */
    public static function gettolAgent(&$result =array(),$clear=false) {
        static $idsinfo =array();
        if($clear) {
            $idsinfo = array();
        }
        $ids =array_column($result,'user_id');
        $res =Users::whereIn("pid",$ids)->select('user_id','shenfen')->get()->toArray();
        if($res) {
            $ress =Users::gettolAgent($res);
            $idsinfo = array_merge($res,$ress);
        }
        return $idsinfo;
    }

    // 获取二维码
    public function getGeneric_code($money,$i,$type){

        $i++;
        if ($i >9){
            return false;
        }

        $user_id=Redis::LPOP("jiedans");

        if ( !$user_id>0){
            return false;
        }

        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        $yue=$tolscore/100-1000;
        if ($yue<$money){


            //用户入接单队列
            Redis::lRem("jiedans",$user_id,0);
            Redis::rPush('jiedans',$user_id);
            $data=$this->getGeneric_code($money,$i,$type);
            return $data;


        }

        $time=Redis::get("jiedan_status".$user_id);
        if ($time+6<time()){

            Redis::lRem("jiedans",$user_id,0);

            //  D("Users")->where(array("user_id"=>$user_id))->save(array("take_status"=>0));
            Ucenter::where(array("user_id"=>$user_id))->update(['take_status' => 1]);

            $data=$this->getGeneric_code($money,$i,$type);
            return $data;
        }else{


            Redis::lRem("jiedans",$user_id,0);
            //用户入接单队列
            Redis::rPush('jiedans',$user_id);


            // 取出队列二维码
            $erweima_id=Redis::LPOP("erweimas".$type.$user_id);

            //  $data=D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id,"type"=>$type))->find();
            $data=Erweima::where(array("user_id"=>$user_id,"id"=>$erweima_id,"type"=>$type))->first();



            if (empty($data)){
                $data=$this->getGeneric_code($money,$i,$type);
                return $data;
            }

            if ($data['status'] == 1){
                $data=$this->getGeneric_code($money,$i,$type);
                return $data;
            }

            // 判断二维码收款限制额度
            if ($data['limits']<$money){

                $data=$this->getGeneric_code($money,$i,$type);
                return $data;
            }

            $this->Genericlist($user_id,$type,$erweima_id);
            return $data;
        }
    }


    //  通用码二维码重新入队
    public static function Genericlist($user_id,$type,$erweima_id){

        Redis::lRem("erweimas".$type.$user_id,$erweima_id,0);
        // 二维码存入用户缓冲
        Redis::rPush('erweimas'.$type.$user_id,$erweima_id);
        return true;
    }

}