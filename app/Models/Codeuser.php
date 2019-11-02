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

}