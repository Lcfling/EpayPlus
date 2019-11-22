<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
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
        return Busdraw::where('id',$id)->update(['status'=>1,'endtime'=>time()]);
    }

    /**
     * 驳回
     */
    public static function reject($id,$remark){
        return Busdraw::where('id',$id)->update(['status'=>2,'remark'=>$remark,'endtime'=>time()]);
    }

}