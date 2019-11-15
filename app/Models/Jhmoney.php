<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/6
 * Time: 18:10
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Jhmoney extends Model {
    protected  $table = 'jhmoney';
    public $timestamps = false;
    /**激活返佣10级返佣
     * @param $user_id 码商id
     * @param $i 返佣级别
     * @param string $accounttable 码商账单表名称
     * @return bool
     */
    public static function jihuofy($user_id,$i,$accounttable='') {
        if ($i >10) {
            return false;
        }

        if ( !($user_id >0)) {
            return false;
        }
        $jhmoney=Jhmoney::where(array('id'=>1))->first();
        $jhfy="fymoney".$i;
        if(empty($accounttable)){
            $accounttable=Accountlog::getdaytable();
        }
        $data['user_id']=$user_id;
        $data['score']=$jhmoney[$jhfy];
        $data['status']=5;
        $data['remark']="激活佣金";
        $data['creatime']=time();
        $accounttable->insert($data);
        Userscount::where(array("user_id"=>$user_id))->increment('balance',$jhmoney[$jhfy],['tol_sore'=>DB::raw("tol_sore + $jhmoney[$jhfy]"),'tol_brokerage'=>DB::raw("tol_brokerage + $jhmoney[$jhfy]")]);
        $userinfo=Users::where(array("user_id"=>$user_id))->first();
        if ($userinfo['pid']>0) {
            $i++;
            $data=Jhmoney::jihuofy($userinfo['pid'],$i,$accounttable);
            return $data;
        } else {
            return false;
        }
    }

}