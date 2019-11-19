<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Busdrawreject extends Model
{
    protected $table = "business_drawreject";
    protected $primaryKey = 'id';
    protected $fillable = ['id','business_code','order_sn','name','deposit_name','deposit_card','money','status','creatime','endtime',];
    public $timestamps = false;
}
