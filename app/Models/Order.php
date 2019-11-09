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
        $orderarr = explode('e',$order_sn);
        $Order =new Order;
        $order =$Order->setTable('order_'.$orderarr[1]);
        return $order;
    }

}