<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
class Order extends Model
{
    protected $table;
    public $timestamps = false;

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
    /**根据订单号获取资金表名称
     * @param $order_sn
     * @return Billflow
     */
    public static function getcounttable($order_sn){
        $orderarr = substr($order_sn,0,8);

        $account =new Billflow();
        $account->setTable('account_'.$orderarr);
        return $account;
    }

    /**
     * @param $functions
     * @return bool
     */
    public static function orderlock($functions){
        $code=time().rand(100000,999999);
        //随机锁入队
        Redis::rPush("order_deal_lock_".$functions,$code);

        //随机锁出队
        $codes=Redis::LINDEX("order_deal_lock_".$functions,0);
        if ($code != $codes){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param $functions
     */
    public static function unorderlock($functions){
        Redis::del("order_deal_lock_".$functions);
    }
}
