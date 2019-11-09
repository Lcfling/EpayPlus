<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/2
 * Time: 16:12
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Accountlog extends Model {
    protected  $table;
    public $timestamps = false;
    public static function getbrokeragelist($tablesuf,$user_id,$lastid,$status){
        $Accountlog =new Accountlog;
        $Accountlog->setTable('account_'.$tablesuf);
        if($lastid){
            $where =[['status',$status],['user_id',$user_id],['id','<',$lastid]];
        }else{
            $where = array('status' => $status, 'user_id' =>$user_id);
        }
        return $Accountlog->orderBy('id','desc')->where($where)->limit(10)->get();

    }

    public static function getcountlist($tablesuf,$user_id,$lastid){
        $Accountlog =new Accountlog;
        $Accountlog->setTable('account_'.$tablesuf);
        if($lastid){
            $where = [['user_id',$user_id],['id','<',$lastid]];
        }else{
            $where = array('user_id' =>$user_id);
        }
        return $Accountlog->orderBy('id','desc')->where($where)->limit(10)->get();
    }

    /**根据订单号获取资金表名称
     * @param $order_sn
     * @return Accountlog
     */
    public static function getcounttable($order_sn){
        $orderarr = explode('e',$order_sn);
        $Accountlog =new Accountlog;
        $Accountlog->setTable('account_'.$orderarr[0]);
        return $Accountlog;
    }

    /**获取当天表名称
     * @return Accountlog
     */
    public static function getdaytable(){
        $tablesuf = date('Ymd');
        $Accountlog =new Accountlog;
        $Accountlog->setTable('account_'.$tablesuf);
        return $Accountlog;
    }

}