<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Business extends Model
{
    protected $table = 'business';
    protected $primaryKey = 'business_code';
    protected $fillable = ['nickname','account','password','accessKey','mobile','fee','paypassword','creatime'];
    protected $businessInfo;
    public $timestamps = false;

    /**
     * 添加判断商户名是否存在
     */
    public static function add_account($account){
       return Business::where(array('account'=>$account))->exists();

    }
    /**
     * 添加判断手机号是否存在
     */
    public static function add_mobile($mobile){
        return Business::where(array('mobile'=>$mobile))->exists();

    }
    /**
     * 编辑判断商户名是否存在
     */
    public static function edit_account($id,$account){
        return Business::where(array('account'=>$account))->whereNotIn('business_code',[$id])->exists();

    }
    /**
     * 编辑判断手机号是否存在
     */
    public static function edit_mobile($id,$mobile){
        return Business::where(array('mobile'=>$mobile))->whereNotIn('business_code',[$id])->exists();

    }
    /**
     * 编辑代理商是否存在
     */
    public static function is_agent($agent_id){
        return DB::table('agent')->where('id','=',$agent_id)->exists();
    }
}