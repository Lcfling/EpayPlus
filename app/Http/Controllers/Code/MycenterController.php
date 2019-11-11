<?php
/**
 * Created by PhpStorm.
 * User: LK
 * Date: 2019/11/1
 * Time: 14:19
 */
namespace App\Http\Controllers\Code;

use App\Models\Accountlog;
use App\Models\Erweimacount;
use App\Models\Imsi;
use App\Models\Order;
use App\Models\Orderrecord;
use App\Models\Users;
use App\Models\Userscount;
use App\Models\Verificat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Erweima;
use App\Models\Withdraw;
class MycenterController extends CommonController {

    /**
     * 修改银行卡信息
     */
    public  function saveuserinfo(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $banknum=(int)$_POST['banknum'];
            $bankname=htmlspecialchars($_POST['bankname']);
            if ( empty($banknum) || empty($bankname)) {
                ajaxReturn(null,'数据异常!',0);
            }
            $data['deposit_name']=$bankname;
            $data['deposit_card']=$banknum;
            $savestatus=Users::where(array("user_id"=>$user_id))->update($data);
            if ($savestatus) {
                ajaxReturn(null,'修改成功!');
            } else {
                ajaxReturn(null,'修改失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /*
     * 同城地址
    */
    public function home() {
        $user_id = $this->uid;
        $home=htmlspecialchars($_POST['home']);
        if (empty($home)) {
            ajaxReturn('','数据异常!',0);
        }
        $savestatus=Users::where(array("user_id"=>$user_id))->update(array("home"=>$home));
        if($savestatus) {
            ajaxReturn('','设置成功!',1);
        } else {
            ajaxReturn('','设置失败!',0);
        }
    }
    /**
     *我的账户
     */
    public function  getaccount(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $scoreinfo = Userscount::gettolscore($user_id);
            $balance = $scoreinfo['balance'];//余额
            $tolscore = $scoreinfo['tol_sore'];//总分
            $tolbrokerage = $scoreinfo['tol_brokerage'];//总利润
            $daybrokerage = $scoreinfo['day_brokerage'];//当天利润
            $djmoney = $scoreinfo['freeze_money'];//冻结金额
            $wxQRnum =Erweima::where(array('user_id'=>$user_id,'status'=>0,'type'=>1))->count();
            $zfbQRnum =Erweima::where(array('user_id'=>$user_id,'status'=>0,'type'=>2))->count();
            $data =array(
                'balance'=>$balance/100,
                'tolscore'=>$tolscore/100,
                'tolbrokerage'=>$tolbrokerage/100,
                'daybrokerage'=>$daybrokerage/100,
                'wxQRnum'=>$wxQRnum,
                'zfbQRnum'=>$zfbQRnum,
                'djmoney'=>$djmoney/100
            );
            ajaxReturn($data,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }

    }
    /**
     *个人信息
     */
    public function  getuserinfo() {
        ajaxReturn($this->member,'请求成功!',1);
    }
    /**
     * 码商充值记录
     */
    public function recharge_list(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $lastid = $request->input('lastid');
            if($lastid){
                $where =[['user_id',$user_id],['status',1],['id','<',$lastid]];
            }else{
                $where =array('user_id'=>$user_id,'status'=>1);
            }
            $daytable = Accountlog::getdaytable();
            $list =$daytable->where($where)->orderBy('id',"desc")->select('id','score','status','creatime')->limit(10)->get();
            foreach ($list as $k=>&$v) {
                $v['money'] = $v['score']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
            }
            ajaxReturn($list,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**提现校验
     * @param Request $request
     */
    public function withdraw_check(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $data =$request->input();
            $money = (int)($data['money'] * 100);
            $scoreinfo = Userscount::gettolscore($user_id);
            $userinfo =Users::where(array('user_id'=>$user_id))->first();
            if($money > $scoreinfo['tol_sore']) {
                ajaxReturn('','提现金额大于总金额!',0);
            }
            if(empty($userinfo->zf_pwd)) {
                ajaxReturn('','未设置支付密码,请去设置!',2);
            }
            if($userinfo->take_status == 1) {
                ajaxReturn('','你处于接单状态,无法提现!',0);
            }
            ajaxReturn('','请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 提现
     */
    public function withdraw(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $userinfo =$this->member;
            $money = (int)($_POST['money'] * 100);
            $zf_pwd = md5($_POST['zf_pwd']);
            $scoreinfo = Userscount::gettolscore($user_id);
            if($money > $scoreinfo['tol_sore']) {
                ajaxReturn('','提现金额大于总金额!',0);
            }
            if($money<0) {
                ajaxReturn('','提现金额有误,请重新输入!',0);
            }
            if(empty($userinfo['zf_pwd'])) {
                ajaxReturn('','未设置支付密码,请去设置!',2);
            }
            if($userinfo['take_status'] == 1) {
                ajaxReturn('','你处于接单状态,无法提现!',0);
            }
            if($userinfo['zf_pwd'] != $zf_pwd) {
                ajaxReturn('','密码错误,请重新输入!',0);
            }
            $data =array(
                'user_id'=>$user_id,
                'order_no'=>getorderId_three(),
                'mobile'=>$userinfo['mobile'],
                'money'=>$money,
                'wx_name'=>$userinfo['wx_name'],
                'name'=>$userinfo['name'],
                'deposit_name'=>$userinfo['deposit_name'],
                'deposit_card'=>$userinfo['deposit_card'],
                'creatime'=>time(),
            );
            $res = DB::table('withdraw')->insert($data);
            if($res) {
                $data1 = array(
                    'user_id'=>$user_id,
                    'score'=>-$money,
                    'status'=>6,
                    'remark'=>"提现",
                    'creatime'=>time(),
                );
                $tablesuf = date('Ymd');
                DB::table('account_'.$tablesuf)->insert($data1);
                ajaxReturn('','已提交!',1);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 提现列表
     */
    public function withdraw_list(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $lastid = $request->input('lastid');
            if($lastid){
                $where =[['user_id',$user_id],['id','<',$lastid]];
            }else{
                $where =array('user_id'=>$user_id);
            }
            $list = Withdraw::where($where)->orderBy('id','desc')->limit(10)->get();
            foreach ($list as $k=>&$v) {
                $v['money']=$v['money']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['withdraw_time']= date('Y/m/d H:i:s',$v['withdraw_time']);
            }
            ajaxReturn($list,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 设置支付密码
     */
    public function setpass(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $pass = md5($_POST['pass']);
            $savestatus = Users::where(array('user_id'=>$user_id))->update(array('zf_pwd'=>$pass));
            if($savestatus) {
                ajaxReturn('','设置成功!',1);
            } else {
                ajaxReturn('','设置失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 设置登录密码
     */
    public function setlogpass(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $logpass=$_POST['second_pwd'];
            $logpass = md5($logpass);
            $savestatus = Users::where(array('user_id'=>$user_id))->save(array('password'=>$logpass));
            if($savestatus) {
                ajaxReturn('','设置成功!',1);
            } else {
                ajaxReturn('','设置失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 实名认证
     */
    public function real_name(Request $request) {
        if($request->isMethod('post')) {
            $data = $_POST;
            $user_id = $this->uid;
            $data['mobile']=htmlspecialchars($data['mobile']);
            if(empty($data['mobile'])) {
                ajaxReturn('','手机号不能为空!',0);
            }
            $data['wx_name']=htmlspecialchars($data['wx_name']);
            if(empty($data['wx_name'])) {
                ajaxReturn('','微信名称不能为空!',0);
            }
            $data['name']=htmlspecialchars($data['name']);
            if(empty($data['name'])) {
                ajaxReturn('','真实姓名不能为空!',0);
            }
            $data['deposit_name']=htmlspecialchars($data['deposit_name']);
            if(empty($data['deposit_name'])) {
                ajaxReturn('','银行卡名称不能为空!',0);
            }
            $data['deposit_card']=htmlspecialchars($data['deposit_card']);
            if(empty($data['deposit_card'])) {
                ajaxReturn('','银行卡号不能为空!',0);
            }
            $savestatus = Users::where(array('user_id'=>$user_id))->update($data);
            if($savestatus) {
                ajaxReturn('','更改成功!',1);
            } else {
                ajaxReturn('','更改失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 二维码管理展示
     */
    public function qrcode(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $listmin = (int)$_POST['min'];
            $listmax = (int)$_POST['max'];
            $type =(int)$_POST['type'];
            $qrcodeinfo = Erweima::where(array('status'=>0,'user_id'=>$user_id,'min'=>$listmin,'max'=>$listmax,'type'=>$type))->get()->toArray();
            if($qrcodeinfo) {
                foreach ($qrcodeinfo as $k=>&$v) {
                    $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
                    //已跑总额
                    $sumscore=Erweimacount::where(array("user_id"=>$user_id,"erweima_id"=>$v['id']))->value("sumscore");
                    $v['sumscore']=abs($sumscore/100);
                    //拼接图片地址
                    $v['erweima']=$this->imgurl.$v['erweima'];
                }
                ajaxReturn($qrcodeinfo,'请求成功!',1);
            } else {
                ajaxReturn('','暂无数据!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 二维码删除管理展示
     */
    public function qrcodes(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $listmin =(int) $_POST['min'];
            $listmax =(int) $_POST['max'];
            $type = (int)$_POST['type'];
            $qrcodeinfo = Erweima::where(array('status'=>1,'user_id'=>$user_id,'min'=>$listmin,'max'=>$listmax,'type'=>$type))->orderBy('creatime','desc')->get()->toArray();
            if($qrcodeinfo) {
                foreach ($qrcodeinfo as $k=>&$v) {
                    $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
                    $v['savetime'] = date('Y/m/d H:i:s',$v['savetime']);
                    //已跑总额
                    $sumscore=Erweimacount::where(array("user_id"=>$user_id,"erweima_id"=>$v['id']))->value("sumscore");
                    $v['sumscore']=abs($sumscore/100);
                    //拼接图片地址
                    $v['erweima']=$this->imgurl.$v['erweima'];
                }
                ajaxReturn($qrcodeinfo,'请求成功!',1);
            } else {
                ajaxReturn('','暂无数据!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     *二维码删除
     */
    public function qrcodedel(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $id =(int) $_POST['id'];
            if($list =Erweima::where(array('id'=>$id,'user_id'=>$user_id,'status'=>0))->first()) {
                $savestatus = Erweima::where(array('id'=>$id,'user_id'=>$user_id,'status'=>0))->update(array('status'=>1,'savetime'=>time()));
                if($savestatus) {
                    Redis::lRem("erweimas".$list['type'].$user_id,$id,0);
                    ajaxReturn('','删除成功!',1);
                } else {
                    ajaxReturn('','删除失败!',0);
                }
            }else {
                ajaxReturn('','无此条记录!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 设置 更改支付密码  验证手机验证码
     */
    public function verification() {
        $code=(int)$_POST['code'];
        //验证码
        $useinfo =$this->member;
        $mobile = htmlspecialchars($useinfo['account']);
        $this->verifycat($mobile,'zfpass_code_',$code);
    }
    /**
     * 发送 更改支付密码验证码
     */
    public function sendcode() {
        $useinfo =$this->member;
        $mobile = htmlspecialchars($useinfo['account']);
        $this->csendcode($mobile,'zfpass_code_',1);
    }
    /**
     * 设置登录密码 验证手机验证码
     */
    public function logpassverify() {
        $code=(int)$_POST['code'];
        //验证码
        $useinfo =$this->member;
        $mobile = htmlspecialchars($useinfo['account']);
        $this->verifycat($mobile,'logpass_code_',$code);
    }
    /**
     * 设置登录密码 发送验证码
     */
    public function logpasssendcode() {
        $useinfo =$this->member;
        $mobile = htmlspecialchars($useinfo['account']);
        $this->csendcode($mobile,'logpass_code_',2);
    }
    /**
     * 创建邀请码
     */
    public function createcode(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $useinfo =$this->member;
            $imsinum =Imsi::getprinum($user_id);
            if($useinfo['shenfen']>14) {
                ajaxReturn('','无权限!',0);
            }
            if($useinfo['jh_status'] == 0) {
                ajaxReturn('','请先激活账户!',0);
            }
            if((int)$imsinum >= (int)$useinfo['imsi_num'] ) {
                ajaxReturn('','生成码已达到上限!',0);
            }
            if($useinfo['shenfen']==1) {
                $code =$this->generateCode(2,1);
            } else {
                $code =$this->generateCode(2,3);
            }
            $data=array(
                'user_id'=>$user_id,
                'code'=>$code,
                'grade'=>$useinfo['shenfen'] + 1,
                'status'=>0,
                'creatime'=>time()
            );
            $addtatus = Imsi::insert($data);
            if($addtatus) {
                ajaxReturn($code,'生成成功!',1);
            } else {
                ajaxReturn('','生成失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 邀请码列表
     */
    public function codelist(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $imsilist = Imsi::where(array('user_id'=>$user_id,'status'=>0))->get()->toArray();
            foreach ($imsilist as $k=>&$v) {
                $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
            }
            ajaxReturn($imsilist,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 下发码数量
     */
    public function issuecode(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $useinfo =$this->member;
            $issuenum = (int)$_POST['issuenum'];
            $bind_id =(int) $_POST['bind_id'];
            $imsiprinum =Imsi::getprinum($user_id);
            if(empty($issuenum)) {
                ajaxReturn('','未填写邀请码数量!',0);
            }
            if(empty($bind_id)) {
                ajaxReturn('','下发的代理商id未填写!',0);
            }
            if((int)$issuenum > $useinfo['imsi_num'] - (int)$imsiprinum) {
                ajaxReturn('','邀请码数量不够!',0);
            }
            Users::where(array('user_id'=>$bind_id))->increment('imsi_num',$issuenum);
            $savestatus =Users::where(array('user_id'=>$user_id))->decrement('imsi_num',$issuenum);
            if($savestatus) {
                ajaxReturn('','下发成功!',1);
            } else {
                ajaxReturn('','下发失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 微信费率
     * 更改分润
     */
    public function changepro(Request $request) {
        if($request->isMethod('post')) {
            $useinfo =$this->member;
            $pronum = (double)$_POST['pronum'];
            $bind_id = (int)$_POST['bind_id'];
            if($pronum < 0.002  ) {
                ajaxReturn('','费率不能低于0.2%!',0);
            }
            if($pronum >= $useinfo['rate'] ) {
                ajaxReturn('','费率不能超过自己的!',0);
            }
            $bind_info=Users::where(array('user_id'=>$bind_id))->first();
            if ($bind_info['rate'] >=$pronum) {
                ajaxReturn('','费率不能低于或者等于当前的费率!',0);
            }
            $saverate =Users::where(array('user_id'=>$bind_id))->update(array('rate'=>$pronum));
            if($saverate) {
                ajaxReturn('','费率更改成功!',1);
            } else {
                ajaxReturn('','费率更改失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 支付宝费率
     * 更改分润
     */
    public function changepros(Request $request) {
        if($request->isMethod('post')) {
            $useinfo =$this->member;
            $pronums = (double)$_POST['pronums'];
            $bind_id = (int)$_POST['bind_id'];
            if( $pronums<0.002 ) {
                ajaxReturn('','费率不能低于0.2%!',0);
            }
            if( $pronums >= $useinfo['rates']) {
                ajaxReturn('','费率不能超过自己的!',0);
            }
            $bind_info=Users::where(array('user_id'=>$bind_id))->first();
            if ($bind_info['rates'] >=$pronums) {
                ajaxReturn('','费率不能低于或者等于当前的费率!',0);
            }
            $saverate =Users::where(array('user_id'=>$bind_id))->update(array('rates'=>$pronums));
            if($saverate) {
                ajaxReturn('','费率更改成功!',1);
            } else {
                ajaxReturn('','费率更改失败!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 分润更改列表 暂时无用
     */
    public function prosavelist(Request $request) {
        if($request->isMethod('post')) {
            $useinfo =$this->member;
            $type=(int)$_POST['type'];
            $bind_id = (int)$_POST['bind_id'];
            $bind_rate =Users::where(array('user_id'=>$bind_id))->value('rate');
            $rate =$useinfo['rate'] * 10000;
            $num =(int)($rate - $bind_rate * 10000)/10;
            $ratearr =array();
            for ( $i=0;$i< 5;$i++ ) {
                if ($type == 1) {
                    $ratenum =$useinfo['rate'] *1000 - $i - 1 ;
                } else {
                    $ratenum =$useinfo['rates'] *1000 - $i - 1 ;
                }
                if((int)$ratenum > 0) {
                    $ratearr[$i]= $ratenum /1000;
                }
            }
            if(!empty($ratearr)) {
                ajaxReturn($ratearr,'请求成功!',1);
            } else {
                ajaxReturn('','当前分润已无法更改!',0);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }

    /**团队列表
     * @param Request $request
     * pernum 直推数量
     * tolnum 总数量
     * surimsinum 剩余码数据
     * rate 微信费率
     * rates 支付宝费率
     * agentlist 直推代理商列表
     */
    public function agent_list(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $useinfo =$this->member;
            $agentlist =Users::where(array('pid'=>$user_id))->get()->toArray();
            $pernum =Users::where(array('pid'=>$user_id))->count();
            $imsinum =Imsi::getprinum($user_id);
            $surimsinum = $useinfo['imsi_num']-$imsinum;
            if($agentlist) {
                $list = Users::gettolAgent($agentlist,true);
                $tolnum = (int)$pernum + count($list);
                $data =array(
                    'pernum'=>$pernum,
                    'tolnum'=>$tolnum,
                    'surimsinum'=>$surimsinum,
                    'rate'=>$useinfo['rate'],
                    'rates'=>$useinfo['rates'],
                    'agentlist'=>$agentlist
                );
                ajaxReturn($data,'请求成功!',1);
            } else {
                $data =array(
                    'pernum'=>0,
                    'tolnum'=>0,
                    'surimsinum'=>$surimsinum,
                    'rate'=>$useinfo['rate'],
                    'rates'=>$useinfo['rates'],
                    'agentlist'=>$agentlist
                );
                ajaxReturn($data,'暂无代理商数据!',1);
            }
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     * 资金列表 type 1 佣金进来  2 冻结中进来
     */
    public function acountlist(Request $request) {
        if($request->isMethod('post')) {
            $user_id = $this->uid;
            $a = $request->input();

            if($request->has('searchtime')){
                $tablesuf = $a['searchtime'];
            }else{
                $tablesuf = date('Ymd');
            }
            $lastid = $a['lastid'];
            $type = $a['type'];
            if($type == 1) {
                $acountlist = Accountlog::getbrokeragelist($tablesuf,$user_id,$lastid,5);
            } elseif($type == 2) {
                if($lastid){
                    $where =[['dj_status',0],['user_id',$user_id],['id','<',$lastid]];
                }else{
                    $where =array('user_id'=>$user_id,'dj_status'=>0);
                }
                $acountlist = Orderrecord::orderBy('id','desc')->where($where)->limit(10)->get();
            } else {
                $acountlist = Accountlog::getcountlist($tablesuf,$user_id,$lastid);
            }
            foreach ($acountlist as $k=>&$v) {
                $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
                $v['money'] = $v['score']/100;
            }
            ajaxReturn($acountlist,'请求成功!',1);
        } else {
            ajaxReturn('','请求数据异常!',0);
        }
    }
    /**生成随机码
     * @param $nums
     * @param $num
     * @return string
     */
    private  function generateCode($nums,$num) {
        $strs="abcdefghjkmnpqrstuvwxyz";
        $str="123456789";
        $keys = "";
        for ($t=0;$t<$nums;$t++) {
            $keys .= $strs {
            mt_rand(0,22)
            }
            ;
        }
        $key = "";
        for ($i=0;$i<31;$i++) {
            $key .= $str {
            mt_rand(0,8)
            }
            ;
        }
        $time  = substr($this->getMillisecond(), 10,3);
        $key = substr($key,3,$num);
        $res = $keys.$key.$time;
        $info = Imsi::where(array('code'=>$res))->first();
        if(!empty($info)) {
            return $this->generateCode($nums,$num);
        } else {
            return $res;
        }
    }
    /**生成毫秒级时间戳
     * @return float
     */
    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
    /**
     * 验证手机验证码
     */
    private function verifycat($mobile,$key,$code) {
        if(!isMobile($mobile)) {
            ajaxReturn('','手机号码格式错误!',0);
        }
        $Cachecode=Redis::get($key.$mobile);
        if($code==$Cachecode) {
            ajaxReturn('','验证成功!',1);
        } else {
            ajaxReturn('','验证码错误!',0);
        }
    }
    /**
     * 发送验证码
     */
    private function csendcode($mobile,$key,$type) {
        if(!isMobile($mobile)) {
            ajaxReturn('','手机号码格式错误!',0);
        }
        $code=rand_string(6,1);
        Redis::set($key.$mobile,$code,300);
        //todo 发送短信
        $res=Verificat::dxbsend($mobile,$code);
        if($res=="0") {
            $this->storecode($code,$mobile,$type);
            ajaxReturn('','发送成功!',1);
        } elseif($res=="123") {
            ajaxReturn('faild','一分钟只能发送一条!',0);
        } else {
            ajaxReturn('faild','失败！请联系管理员:'.$res,0);
        }
    }
    private function storecode($code,$mobile,$type) {
        $data=array(
            'code'=>$code,
            'phone'=>$mobile,
            'type'=>$type,
            'creatime'=>time()
        );
        Verificat::insert($data);
    }

    //修改二级密码
    public function setsecondpwd() {
        $user_id = $this->uid;
        //$code=$_POST['code'];
        $second_pwd=htmlspecialchars($_POST['second_pwd']);
        $resecond_pwd=htmlspecialchars($_POST['resecond_pwd']);
        if($second_pwd=="") {
            ajaxReturn('','二级密码不能为空！',0);
        }
        if($second_pwd!=$resecond_pwd) {
            ajaxReturn('','两次二级密码不相同！',0);
        }
        //判断用户是否存在
        $userInfo=Users::where(array("user_id"=>$user_id))->first();
        $code=(int)$_POST['code'];
        //验证码
        $mobile = htmlspecialchars($userInfo['account']);
        if(!isMobile($mobile)) {
            ajaxReturn('','手机号码格式错误!',0);
        }
        $Cachecode=Redis::get('zfpass_code_'.$mobile);
        if($code!=$Cachecode) {
            ajaxReturn('','验证码错误!',0);
        }
        if($userInfo['password']==md5($second_pwd)) {
            ajaxReturn('','原始密码不能与二级密码相同！',0);
        }
        $second_pwd=md5($second_pwd);
        if(Users::where(array("user_id"=>$user_id))->update(array("second_pwd"=>$second_pwd))) {
            ajaxReturn('','操作成功！');
        } else {
            ajaxReturn('','操作失败！',0);
        }
    }
}