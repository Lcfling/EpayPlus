<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'admin_version';
    protected $fillable = ['version_no','detail','force','is_open','creatime'];
    protected $noticeInfo;
    public $timestamps = false;

    //添加版本号存在
    public static function add_ver($ver_no){
        return Version::where('version_no',$ver_no)->exists();
    }
    //编辑版本号存在
    public static function edit_ver($id,$ver_no){
        return Version::where('version_no',$ver_no)->whereNotIn('id',[$id])->exists();
    }
}
