<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/13
 * Time: 16:51
 */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Businesscount extends Model {
    protected  $table = 'business_count';
    public $timestamps = false;
}