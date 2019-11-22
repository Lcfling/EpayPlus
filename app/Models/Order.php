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

}