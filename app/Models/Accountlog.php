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
        $tablesuf = substr($order_sn,0,8);
        $Accountlog =new Accountlog;
        $Accountlog->setTable('account_'.$tablesuf);
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

    /**创建码商流水表
     * @param $accounttable
     * @return bool
     */
    public static function createaccount($accounttable){
        $accounttable = 'zf_'.$accounttable;
        $status =DB::statement("CREATE TABLE $accounttable (
                                  `id` int(11) NOT NULL AUTO_INCREMENT,
                                  `user_id` int(11) NOT NULL,
                                  `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
                                  `order_sn` char(50) NOT NULL DEFAULT '--' COMMENT '平台订单号',
                                  `erweima_id` int(11) NOT NULL DEFAULT '0' COMMENT '二维码id',
                                  `business_code` int(20) NOT NULL DEFAULT '0' COMMENT '商户标识',
                                  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1 充值  2 第三方支付扣除  3 冻结  4 解冻 5 佣金  6 提现',
                                  `payType` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 1 微信  2支付宝',
                                  `remark` char(20) DEFAULT NULL COMMENT '备注',
                                  `creatime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
                                  PRIMARY KEY (`id`),
                                  KEY `business_code` (`business_code`) USING BTREE,
                                  KEY `user_id` (`user_id`) USING BTREE
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='码商资金流水表';
                    ");
        return$status;
    }

}