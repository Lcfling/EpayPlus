<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/6
 * Time: 16:04
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Agentbillflow extends Model {
    protected  $table;
    public $timestamps = false;
    /**获取代理商资金表名称
     * @param $order_sn
     * @return Businessbillflow
     */
    public static function getagentbftable($order_sn){
        $nyr = substr($order_sn,0,8);
        $weeksuf = computeWeek($nyr);
        $agentbillflow =new Agentbillflow;
        $agentbillflow->setTable('agent_billflow_'.$weeksuf);
        return  $agentbillflow;
    }

    /**创建订单周表
     * @param $ordertable
     * @return bool
     */
    public static function createagentbillflow($agentbillflow){
        $agentbillflow = 'zf_'.$agentbillflow;
        $status =DB::statement("CREATE TABLE $agentbillflow (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `agent_id` int(11) NOT NULL DEFAULT '0' COMMENT '代理商id',
                                      `order_sn` char(50) NOT NULL DEFAULT '--' COMMENT '订单号',
                                      `score` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '积分',
                                      `business_code` int(20) NOT NULL DEFAULT '0' COMMENT '商户标识',
                                      `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1 支付 2利润 3提现',
                                      `paycode` int(3) NOT NULL DEFAULT '0' COMMENT '类型 1 微信  2支付宝',
                                      `remark` char(10) DEFAULT NULL COMMENT '备注',
                                      `creatime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
                                      `tradeMoney` int(11) DEFAULT '0' COMMENT '实际到账金额',
                                      PRIMARY KEY (`id`),
                                      KEY `business_code` (`business_code`) USING BTREE,
                                      KEY `agent_id` (`agent_id`) USING BTREE,
                                      KEY `order_sn` (`order_sn`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='代理商资金流（代理商分红）';
                    ");
        return$status;
    }

}