<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Codecount extends Model
{
    protected $table='users_count';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','balance','tol_sore'];
    public $timestamps = false;
}
