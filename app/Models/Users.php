<?php
/**
 * Created by PhpStorm.
 * User: LK,JJ
 * Date: 2019/11/1
 * Time: 17:23
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class Users extends Model {
    protected  $table = 'users';
    public $timestamps = false;
    /**获取下面所有人
     * @param array $result
     * @return array
     */
    public static function gettolAgent(&$result =array(),$clear=false) {
        static $idsinfo =array();
        if($clear) {
            $idsinfo = array();
        }
        $ids =array_column($result,'user_id');
        $res =Users::whereIn("pid",$ids)->select('user_id','shenfen')->get()->toArray();
        if($res) {
            $ress =Users::gettolAgent($res);
            $idsinfo = array_merge($res,$ress);
        }
        return $idsinfo;
    }

}