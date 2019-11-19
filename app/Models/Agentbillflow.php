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

}