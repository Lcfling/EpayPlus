<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Callcenter extends Model
{
    protected $table = 'kefu';
    protected $fillable = ['content','url','creatime'];
    public $timestamps = false;
}
