<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/6
 * Time: 15:16
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Businessbillflow extends Model {
    protected  $table;
    public $timestamps = false;

    /**获取商户资金表名称
     * @param $order_sn
     * @return Businessbillflow
     */
    public static function getbusbftable($order_sn){
        $nyr = substr($order_sn,0,8);
        $weeksuf = computeWeek($nyr);
        $Businessbillflow =new Businessbillflow;
        $Businessbillflow->setTable('business_billflow_'.$weeksuf);
        return $Businessbillflow;
    }

    /**创建订单周表
     * @param $ordertable
     * @return bool
     */
    public static function createbusinessbillflow($businessbillflow){
        $businessbillflow = 'zf_'.$businessbillflow;
        $status =DB::statement("CREATE TABLE $businessbillflow (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `order_sn` char(50) NOT NULL DEFAULT '--' COMMENT '订单号',
                                      `score` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '积分/提现金额',
                                      `tradeMoney` int(11) DEFAULT '0' COMMENT '扣费率后 分/实际支付金额(扣除手续费后)',
                                      `business_code` int(20) NOT NULL DEFAULT '0' COMMENT '商户标识',
                                      `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1 支付 2利润3提现',
                                      `paycode` int(3) NOT NULL DEFAULT '0' COMMENT '类型 1 微信  2支付宝',
                                      `remark` char(10) NOT NULL COMMENT '备注',
                                      `creatime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
                                      PRIMARY KEY (`id`),
                                      KEY `business_code` (`business_code`) USING BTREE,
                                      KEY `order_sn` (`order_sn`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='商户支付资金流';
                    ");
        return$status;
    }


}