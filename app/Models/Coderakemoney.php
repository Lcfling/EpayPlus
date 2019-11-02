<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coderakemoney extends Model
{
    protected $table = 'jhmoney';
    protected $fillable = ['jhmoney','fymoney1','fymoney2','fymoney3','fymoney4','fymoney5','fymoney6','fymoney7','fymoney8','fymoney9','fymoney10',];
    protected $coderakemoneyInfo;
    public $timestamps = false;
    protected $primaryKey = 'id';


}
