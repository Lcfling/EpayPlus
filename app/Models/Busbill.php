<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Busbill extends Model
{
    protected $table='business_billflow';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
