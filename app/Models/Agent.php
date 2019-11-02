<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'agent';
    protected $primaryKey = 'id';
    protected $fillable = ['agent_name','account','password','mobile','fee','creatime','updatetime'];
    protected $agentInfo;
    public $timestamps = false;

    /**
     * 添加判断商户名是否存在
     */
    public static function add_account($account){
        return Agent::where(array('account'=>$account))->exists();

    }
    /**
     * 添加判断手机号是否存在
     */
    public static function add_mobile($mobile){
        return Agent::where(array('mobile'=>$mobile))->exists();

    }
    /**
     * 编辑判断商户名是否存在
     */
    public static function edit_account($id,$account){
        return Agent::where(array('account'=>$account))->whereNotIn('id',[$id])->exists();

    }
    /**
     * 编辑判断手机号是否存在
     */
    public static function edit_mobile($id,$mobile){
        return Agent::where(array('mobile'=>$mobile))->whereNotIn('id',[$id])->exists();

    }
}
