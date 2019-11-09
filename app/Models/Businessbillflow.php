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
        $orderarr = explode('e',$order_sn);
        $Businessbillflow =new Businessbillflow;
        $Businessbillflow->setTable('business_billflow_'.$orderarr[1]);
        return $Businessbillflow;
    }


}