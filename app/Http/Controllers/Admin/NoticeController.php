<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoticeController extends Controller
{
    /**
     * 公告列表
     */
    public function index(Request $request)
    {
       return view('notices.list');
    }
}
