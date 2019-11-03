<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Rechargelist extends Model
{
    protected $table = 'czrecord';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['name','user_id','score','czimg','sk_name','sk_banknum','sk_bankname','status','creatime'];
}