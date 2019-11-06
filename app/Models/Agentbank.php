<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentbank extends Model
{
    protected $table = 'agent_bank';
    protected $fillable = ['agent_id','name','deposit_name','deposit_card','creatime'];
    public $timestamps = false;

    /**
     * 添加效验银行卡唯一
     */
    public static function add_bank($banknum){
        return Recharge::where('deposit_card',$banknum)->exists();
    }
}
