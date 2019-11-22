<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Codedraw extends Model
{
    protected $table = "withdraw";
    protected $primaryKey = 'id';
    protected $fillable = ['id','user_id','name','deposit_name','deposit_card','money','status','creatime'];
    public $timestamps = false;

    /**
     * 通过
     */
    public static function pass($id){
        return Codedraw::where('id',$id)->update(['status'=>1,'endtime'=>time()]);
    }

    /**
     * 驳回
     */
    public static function reject($id,$remark){
        return Codedraw::where('id',$id)->update(['status'=>2,'remark'=>$remark,'endtime'=>time()]);
    }
}
