<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buscount extends Model
{
    protected $table='business_count';
    protected $primaryKey = 'id';
    //protected $billflowInfo;
    public $timestamps = false;
}
