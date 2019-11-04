<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agentbill extends Model
{
    protected $table='agent_billflow';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
