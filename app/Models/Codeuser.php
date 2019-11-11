<?php


namespace App\Models;


use App\Models\Traits\AdminCodeUserTrait;
use Illuminate\Database\Eloquent\Model;

class Codeuser extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id','account','mobile','pid','shenfen','take_status','rate','rates','jh_status'];
    protected $codeUserInfo;
    public $timestamps = false;

    /**
     * 添加判断存在
     */
    public static function add_unique($account){
        $res=Codeuser::where(array('account'=>$account))->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 编辑判断存在
     */
    public static function edit_unique($id,$account){
        $res=Codeuser::where(array('account'=>$account))->whereNotIn('user_id',[$id])->exists();
        if($res){
            return true;
        }else{
            return false;
        }
    }
}