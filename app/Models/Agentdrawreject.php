<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Agentdrawreject extends Model
{
    protected $table = "agent_drawreject";
    protected $primaryKey = 'id';
    protected $fillable = ['id','agent_id','order_sn','name','deposit_name','deposit_card','money','status','creatime','endtime',];
    public $timestamps = false;
}
