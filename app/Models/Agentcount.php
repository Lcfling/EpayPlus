<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/13
 * Time: 17:02
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Agentcount extends Model {
    protected  $table = 'agent_count';
    public $timestamps = false;
}