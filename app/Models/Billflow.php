<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billflow extends Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','order_id','score','erweima_id','business_code','out_uid','status','payType','creatime'];
    protected $billflowInfo;
    public $timestamps = false;
}
