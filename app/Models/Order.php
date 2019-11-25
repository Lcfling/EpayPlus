<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/2
 * Time: 16:04
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class Order extends Model {
    public $timestamps = false;

    /**获取本周冻结订单
     * @param $user_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getorderlist($user_id){
        $weeksuf = computeWeek(time(),false);
        $Order =new Order;
        $Order->setTable('order_'.$weeksuf);
        return $Order->where(array('user_id'=>$user_id,'dj_status'=>0))->paginate(10);
    }

    /**自定义订单表后缀 默认当前时间
     * @param string $weeksuf
     * @return Order
     */
    public static function getordertable($weeksuf=''){
        if(empty($weeksuf)){
            $weeksuf = computeWeek(time(),false);
        }
        $Order =new Order;
        $order =$Order->setTable('order_'.$weeksuf);
        return $order;
    }

    /**根据订单号获取订单表名称
     * @param $order_sn
     * @return Order
     */
    public static function getordersntable($order_sn){
        $nyr = substr($order_sn,0,8);
        $weeksuf = computeWeek($nyr);
        $Order =new Order;
        $order =$Order->setTable('order_'.$weeksuf);
        return $order;
    }

    /**订单数据分页
     * @param $where
     * @param $count
     * @param string $weeksuf
     * @param string $orderlist
     * @return array
     */
    public static function getorderinfo($where,$count,$weeksuf='')
    {
        if($weeksuf){
            $ordertatle = Order::getordertable($weeksuf);
        }else{
            $weeksuf = computeWeek(time(),false);
            $ordertatle = Order::getordertable($weeksuf);
        }
        $ordertatlepre = 'order_'.($weeksuf-1);

        $list=$ordertatle->where($where)->orderBy('id', 'desc')->limit($count)->get()->toArray();
        if(count($list)<$count && Schema::hasTable($ordertatlepre)){
            $Newcount=$count-count($list);
            $orderlist = Order::getorderinfo($where,$Newcount,$weeksuf - 1);
            $list = array_merge($list,$orderlist);
            return $list;
        }else{
            return $list;
        }
    }

    /**审查昨天和今天是2个表
     * @param $where
     */
    public static function chackweek($where,$count){
        $weeksuf = computeWeek(time(),false);
        $weeksufpre = computeWeek(time()-86410,false);
        if($weeksuf == $weeksufpre){
            $ordertatle = Order::getordertable($weeksuf);
            return $ordertatle->where($where)->orderBy('pay_time','asc')->limit($count)->get()->toArray();
        }else{
            return Order::timegetorderinfo($where,$count,$weeksufpre);
        }
    }

    /**定时器调用订单数据查询
     * @param $where
     * @param $count
     * @param $weeksufpre
     * @return array
     */
    public static function timegetorderinfo($where,$count,$weeksufpre)
    {
        $ordertatle = Order::getordertable($weeksufpre);

        $ordertatlepre = 'order_'.($weeksufpre+1);

        $list=$ordertatle->where($where)->orderBy('pay_time', 'asc')->limit($count)->get()->toArray();
        if(count($list)<$count && Schema::hasTable($ordertatlepre)){
            $Newcount=$count-count($list);
            $orderlist = Order::getorderinfo($where,$Newcount,$weeksufpre + 1);
            $list = array_merge($list,$orderlist);
            return $list;
        }else{
            return $list;
        }
    }

    /**
     * 获取进行中订单数据
     */
    public static function getorderinginfo($user_id){
        $weeksuf = computeWeek(time(),false);
        $weeksufpre = computeWeek(time()-86410,false);
        if($weeksuf == $weeksufpre){
            $ordertatle = Order::getordertable($weeksuf);
            return $ordertatle->where(array("user_id"=>$user_id,"status"=>0,"sk_status"=>0))->orderBy('creatime', 'desc')->get()->toArray();
        }else{
            $ordertatle = Order::getordertable($weeksuf);
            $ordertatlepre = Order::getordertable($weeksufpre);
            $first  =$ordertatlepre->where(array("user_id"=>$user_id,"status"=>0,"sk_status"=>0));
            return $ordertatle->where(array("user_id"=>$user_id,"status"=>0,"sk_status"=>0))->union($first)->orderBy('creatime', 'desc')->get()->toArray();
        }

    }

    /**创建订单周表
     * @param $ordertable
     * @return bool
     */
    public static function createorder($ordertable){
        $ordertable = 'zf_'.$ordertable;
        $status =DB::statement("CREATE TABLE $ordertable (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `out_order_sn` char(50) NOT NULL DEFAULT '--' COMMENT '商户订单号',
                                      `order_sn` char(50) NOT NULL DEFAULT '--' COMMENT '平台订单号',
                                      `payType` tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 0 默认  1 微信  2 支付宝',
                                      `tradeMoney` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '订单金额',
                                      `sk_money` decimal(11,0) DEFAULT '0' COMMENT '收款金额',
                                      `erweima_id` int(11) NOT NULL DEFAULT '0' COMMENT '二维码id',
                                      `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '码商id',
                                      `status` int(11) NOT NULL DEFAULT '0' COMMENT '支付状态（0未支付 ，1支付成功 ，2过期  ,3取消）',
                                      `creatime` int(11) NOT NULL DEFAULT '0' COMMENT '创建订单时间',
                                      `pay_time` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
                                      `business_code` int(11) NOT NULL DEFAULT '0' COMMENT '商户code',
                                      `sk_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '码商收款状态 0未收款  1手动收款 2自动收款',
                                      `dj_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '冻结状态（0冻结 1解冻 2已扣除）',
                                      `is_shoudong` tinyint(1) DEFAULT '0' COMMENT '是否手动回调 1手动回调',
                                      `notifyUrl` varchar(255) DEFAULT NULL COMMENT '第三方回调地址',
                                      `callback_status` tinyint(1) DEFAULT '0' COMMENT '第三方回调状态  1 成功  2推送失败',
                                      `callback_num` tinyint(1) DEFAULT '0' COMMENT '推送次数',
                                      `callback_time` int(11) DEFAULT '0' COMMENT '回调时间',
                                      `chanum` int(11) DEFAULT '0' COMMENT '金额差价',
                                      `home` varchar(255) DEFAULT '--' COMMENT '订单城市',
                                      `is_send` tinyint(1) DEFAULT '0' COMMENT '是否已推送给码商接单',
                                      PRIMARY KEY (`id`),
                                      KEY `user_id` (`user_id`) USING BTREE,
                                      KEY `business_code` (`business_code`) USING BTREE,
                                      KEY `out_order_sn` (`out_order_sn`) USING BTREE,
                                      KEY `order_sn` (`order_sn`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='订单表';
                    ");
        return$status;
    }

}