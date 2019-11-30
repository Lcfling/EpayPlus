<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/7
 * Time: 11:04
 */
namespace App\Http\Controllers\Code;

use App\Models\Index;
use App\Models\Kefu;
use App\Models\Message;
use App\Models\Notice;
use Illuminate\Http\Request;
class ZfnoticeController extends CommonController {
    /**
     * 获取公告
     */
    public function index(Request $request) {
        if($request->isMethod('post')) {
            $messageinfo =Notice::orderBy('creattime','desc')->get();
            foreach ($messageinfo as $k=>&$v) {
                $v['creattime']= date('Y/m/d H:i:s',$v['creattime']);
            }
            $data =array(
                'messageinfo'=>$messageinfo
            );
            ajaxReturn($data,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     *获取消息
     */
    public function  message(Request $request) {
        if($request->isMethod('post')) {
            $uid =$this->uid;
            $messageinfo =Message::where(array('user_id'=>$uid))->orderBy('creatime','desc')->get();
            $messagenum = Message::where(array('user_id'=>$uid,'ifread'=>0))->count();
            $data =array(
                'messageinfo'=>$messageinfo,
                'messagenum'=>$messagenum
            );
            ajaxReturn($data,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     *未读更改为已读
     */
    public function  setifread(Request $request) {
        if($request->isMethod('post')) {
            $uid =$this->uid;
            $mas_id =$_POST['mas_id'];
            $statusnum = Message::where(array('user_id'=>$uid,'ifread'=>0,'id'=>$mas_id))->update(array('ifread'=>1));
            ajaxReturn('','请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    // 客服二维码
    public function kefu() {
        $list = Kefu::orderBy('id','desc')->limit(1)->first();
        $list['url']= $this->kefuurl.$list['url'];
        ajaxReturn($list,'请求成功!',1);
    }
    /**
     * 公告
     */
    public function getnotice() {
        $list = Notice::orderBy('id','desc')->limit(1)->first();
        ajaxReturn($list,'请求成功!',1);
    }
}