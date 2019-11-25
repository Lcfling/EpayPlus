<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/25
 * Time: 10:53
 */
namespace App\Http\Controllers\Code;
use App\Models\Accountlog;
use App\Http\Controllers\Controller;
use App\Models\Agentbillflow;
use App\Models\Businessbillflow;
use App\Models\Order;
use App\Models\Verificat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
class TimercreatetableController extends Controller {
    /**
     * 创建码商流水天表
     */
    public function createaccount(Request $request){
        $accounttatle = 'account_'.date("Ymd",strtotime("+1 day"));
            if(Schema::hasTable($accounttatle)){
                ajaxReturn(null, "表已存在!",0);
            }else{
                $status =Accountlog::createaccount($accounttatle);
                if($status){
                    ajaxReturn($status, "$accounttatle 码商流水创建成功!",1);
                }else{
                    Verificat::createtablesend('18638521382',"$accounttatle 码商流水创建失败!");
                    Verificat::insertsendcode('','18638521382',5,'127.0.0.1',0,"$accounttatle 码商流水创建失败!");
                    ajaxReturn($status, "$accounttatle 码商流水创建失败!",0);
                }

            }

    }

    /**
     * 创建订单周表
     */
    public function createorder(Request $request){
        $ordertatle = 'order_'.computeWeek(strtotime("+1 day"),false);
        if(Schema::hasTable($ordertatle)){
            ajaxReturn(null, "表已存在!",0);
        }else{
            $status =Order::createorder($ordertatle);
            if($status){
                ajaxReturn($status, "$ordertatle 订单表创建成功!",1);
            }else{
                Verificat::createtablesend('18638521382',"$ordertatle 订单表创建失败!");
                Verificat::insertsendcode('','18638521382',5,'127.0.0.1',0,"$ordertatle 订单表创建失败!");
                ajaxReturn($status, "$ordertatle 订单表创建失败!",0);
            }
        }

    }

    /**
     * 创建代理商流水表
     */
    public function createagentbf(Request $request){
        $agentbillflow = 'agent_billflow_'.computeWeek(strtotime("+1 day"),false);
        if(Schema::hasTable($agentbillflow)){
            ajaxReturn(null, "表已存在!",0);
        }else{
            $status =Agentbillflow::createagentbillflow($agentbillflow);
            if($status){
                ajaxReturn($status, "$agentbillflow 代理商流水表创建成功!",1);
            }else{
                Verificat::createtablesend('18638521382',"$agentbillflow 代理商流水表创建失败!");
                Verificat::insertsendcode('','18638521382',5,'127.0.0.1',0,"$agentbillflow 代理商流水表创建失败!");
                ajaxReturn($status, "$agentbillflow 代理商流水表创建失败!",0);
            }
        }

    }

    /**
     * 创建商户流水表
     */
    public function createbusinessbf(Request $request){
        $businessbillflow = 'business_billflow_'.computeWeek(strtotime("+1 day"),false);
        if(Schema::hasTable($businessbillflow)){
            ajaxReturn(null, "表已存在!",0);
        }else{
            $status =Businessbillflow::createbusinessbillflow($businessbillflow);
            if($status){
                ajaxReturn($status, "$businessbillflow 商户流水表创建成功!",1);
            }else{
                Verificat::createtablesend('18638521382',"$businessbillflow 商户流水表创建失败!");
                Verificat::insertsendcode('','18638521382',5,'127.0.0.1',0,"$businessbillflow 商户流水表创建失败!");
                ajaxReturn($status, "$businessbillflow 商户流水表创建失败!",0);
            }
        }

    }

}