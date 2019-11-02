<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Busdraw extends Model
{
    protected $table = "business_withdraw";
    protected $primaryKey = 'id';
    protected $fillable = ['id','business_code','name','deposit_name','deposit_card','money','status','creatime'];
    public $timestamps = false;

    /**
     * 通过
     */
    public static function pass($id){
        return Busdraw::where('id',$id)->update(['status'=>1]);
    }

    /**
     * 驳回
     */
    public static function reject($id){
        return Busdraw::where('id',$id)->update(['status'=>2]);
    }

}