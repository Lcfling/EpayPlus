<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Codedrawreject extends Model
{
    protected $table = "users_drawreject";
    protected $primaryKey = 'id';
    protected $fillable = ['id','user_id','order_sn','name','wx_name','mobile','deposit_name','deposit_card','money','status','creatime','endtime',];
    public $timestamps = false;
}
