<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notice';
    protected $fillable = ['title','content'];
    protected $noticeInfo;
    public $timestamps = false;
}
