<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $table = 'czinfo';
    protected $primaryKey = 'id';
    protected $fillable = ['sk_name','sk_bankname','sk_banknum','status','creatime'];
    public $timestamps = false;

    /**
     * 添加校验银行卡唯一
     */
    public static function add_bank($banknum){
        return Recharge::where('sk_banknum',$banknum)->exists();
    }
    /**
     * 编辑校验银行卡唯一
     */
    public static function edit_bank($id,$banknum){
        return Recharge::where('sk_banknum',$banknum)->whereNotIn('id',[$id])->exists();
    }

    public static function getkefu(){
        $uid=Adminrole::where('role_id',4)->select('user_id')->get()->toArray();
        $ids=array_column($uid,'user_id');
        $kefu=User::whereIn('id',$ids)->select('id','username')->get()->toArray();
        return $kefu;
    }
}