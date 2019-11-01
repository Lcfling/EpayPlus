<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'admin_options';
    protected $fillable = ['key','value','content'];
    protected $optionsInfo;
    public $timestamps = false;
    

}
