<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/10/31
 * Time: 16:44
 */
namespace App\Http\Controllers\Code;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Users;
class CommonController extends Controller {
    protected $token = '';
    protected $uid='';
    protected $member = array();
    public $redis=null;
    public $imgurl="http://epp.zgzyph.com";
    public $imgurls="http://epp.zgzyph.com";
    public $kefuurl="http://eppht.zgzyph.com";
    public function __construct() {
        $this->checkLogin();
    }
    //验证用户信息
    public function checkLogin() {
        $user_id = $_SERVER['HTTP_USERID'];
        $token = $_SERVER['HTTP_TOKEN'];
        $userInfo = Users::where(array('user_id' => $user_id))->first();
        if(!$userInfo) {
            $this->ajaxReturn(null, '用户不存在!', 2);
        }
        if ($userInfo['token'] == '') {
            $this->ajaxReturn(null, '登录失效,请重新登录!', 2);
        }
        if ($userInfo['token'] === $token) {
            $this->uid = $user_id;
            $this->member = $userInfo;
            return;
        } else {
            $this->ajaxReturn(null, '登录验证失败,请重新登录!', 2);
        }
    }
    /**
     * Ajax方式返回数据到客户端.
     *
     * @param mixed  $data   要返回的数据
     * @param string $info   提示信息
     * @param bool   $status 返回状态
     * @param string $status ajax返回类型 JSON XML
     */
    public function ajaxReturn($data, $info = '', $status = 1, $type = 'JSON') {
        $result = array();
        $result['status'] = $status;
        $result['info'] = $info;
        $result['data'] = $data;
        if (strtoupper($type) == 'JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($result));
        } elseif (strtoupper($type) == 'XML') {
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($result));
        } elseif (strtoupper($type) == 'EVAL') {
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
        } else {
            // TODO 增加其它格式
        }
    }
}