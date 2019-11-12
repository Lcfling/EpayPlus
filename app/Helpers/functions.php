<?php
use Illuminate\Support\Facades\Redis;
use PragmaRX\Google2FA\Google2FA;

/**
 * 判断是否为不可操作id
 *
 * @param	number	$id	参数id
 * @param	string	$configName	配置名
 * @param	bool  $emptyRetValue
 * @param	string	$split 分隔符
 * @return	bool
 */
if (!function_exists('is_config_id')) {
    function is_config_id($id, $configName, $emptyRetValue = false, $split = ",")
    {
        if (empty($configName)) return $emptyRetValue;
        $str = trim(config($configName, ""));
        if (empty($str)) return $emptyRetValue;
        $ids = explode($split, $str);
        return in_array($id, $ids);
    }
}

/**获取周
 * @param $date
 * @return float
 */
function computeWeek($date,$status = 'true'){
    date_default_timezone_set('PRC');
    if($status){
        $diff = strtotime($date);
    }else{
        $diff = $date;
    }
    $res = ceil(($diff - 1564934399)/(24*60*60*7));
    return $res;
}

/**对象转数组
 * @param $object
 * @return mixed
 */
function objectToArray($object) {
    //先编码成json字符串，再解码成数组
    return json_decode(json_encode($object), true);
}

/**
 * Ajax方式返回数据到客户端.
 *
 * @param mixed  $data   要返回的数据
 * @param string $info   提示信息
 * @param bool   $status 返回状态
 * @param string $status ajax返回类型 JSON XML
 */
function ajaxReturn($data, $info = null, $status = 1, $type = 'JSON') {
    $result = array();
    if(empty($info)){
        $info = null;
    }
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
/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 * @return string
 */
function rand_string($len = 6, $type = '', $addChars = '') {
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {//位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str.= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}

function isMobile($phone_number){
    //@2017-11-25 14:25:45 https://zhidao.baidu.com/question/1822455991691849548.html
    //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
    //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
    //中国电信号码：133、153、180、181、189、177、173、149、199
    $g = "/^1[34578]\d{9}$/";
    $g2 = "/^19[89]\d{8}$/";
    $g3 = "/^166\d{8}$/";
    if(preg_match($g, $phone_number)){
        return true;
    }else  if(preg_match($g2, $phone_number)){
        return true;
    }else if(preg_match($g3, $phone_number)){
        return true;
    }

    return false;

}

/**生成唯一订单号
 * @return string
 */
function getorderId_three(){
    //生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码

    @date_default_timezone_set("PRC");
    //订购日期

    $order_date = date('Y-m-d');

    //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）

    $order_id_main = date('YmdHis') . rand(10000000,99999999);

    //订单号码主体长度

    $order_id_len = strlen($order_id_main);

    $order_id_sum = 0;

    for($i=0; $i<$order_id_len; $i++){

        $order_id_sum += (int)(substr($order_id_main,$i,1));

    }

    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）

    $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $order_id;
}

/**无缓存的唯一订单号
 * @param $paycode 支付类型
 * @param $business_code 商户id
 * @param $tablesuf 订单表后缀
 * @return string
 */
function getrequestId($paycode,$business_code,$tablesuf){

    $requestId  =	date("Ymd").'e'.$tablesuf.'e'.$paycode .substr($business_code, -3).rand(11111111,99999999);

    return $requestId;

}

/**谷歌验证
 * @param $secret 动态码
 * @param $ggkey ggkey
 * @return bool
 * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
 * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
 * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
 */
function verifyGooglex($secret,$ggkey){

    $google2fa = new Google2FA();
    if($google2fa->verifyKey($ggkey, $secret)){
        return true;
    }else{
        return false;
    }
}

/**有缓存的100%唯一订单号 第5种方法
 * @param $paycode 支付类型
 * @param $business_code 商户id
 * @param $tablesuf 订单表后缀
 * @return string
 */
function getUniqueId_six($paycode,$business_code,$tablesuf) {
    $uniqueid =incr_num('UniqueId',1,5);
    $order_no = date('Ymd').'e'.$tablesuf.'e'.$paycode .substr($business_code, -3).$uniqueid;
    return $order_no;
}

/**
 * 生成自增长数字
 *
 * @param string $key 缓存key
 * @param int $step 自增长步长
 * @param int $expires 缓存过期时间，默认当天有效，单位秒
 *
 * @return int $num;
 * @author leeyi <leeyisoft@qq.com>
 */
function incr_num($key = 'ddg', $step = 1, $expires = 0) {
    $cache_key = 'incrnum:'.$key.'_setp:'.$step;
    $num       = Redis::incrBy($cache_key, (int)$step);
    if ($expires>0) {
        $pexpire     = 'pexpire';
        $millisecond = $expires*1000;
    } else {
        $pexpire     = 'pexpireAt';
        $millisecond = $this->get_time_235959()*1000+999;
    }
    if (2>$num) {
        Redis::$pexpire($cache_key, $millisecond); // 设置过期时间
    }
    return $num;
}
/**
 * 根据给定时间戳，获取当天时间最后一秒的时间戳
 * @author leeyi <leeyisoft@qq.com>
 */
function get_time_235959($time = '') {
    $time = empty($time) ? time() : intval($time);
    return strtotime(date('Y-m-d 00:00:00', $time+86400))-1;
}

/**检测数组value值的重复
 * @param $array
 * @return array
 */
function FetchRepeatMemberInArray($array) {
    // 获取去掉重复数据的数组
    $unique_arr = array_unique ( $array );
    // 获取重复数据的数组
    $repeat_arr = array_diff_assoc ( $array, $unique_arr );
    return $repeat_arr;
}

