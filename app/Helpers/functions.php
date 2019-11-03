<?php
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
    if($status){
        $diff = strtotime($date);
    }else{
        $diff = $date;
    }
    $res = ceil($diff/(24*60*60*7));
    return $res;
}