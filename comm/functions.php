<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-6-26
 * Time: 下午1:57
 */

/**
 * 解析|生成URL地址
 * @param string|array $data url地址参数2种写法：['p'=>1] | 'p=2'
 * @param string $global_url 解析目的地址，默认当前页面地址 格式：http://xx.com/helloword?do=hello
 * @return string 返回最终结果url
 * */
function url($data = null, $global_url = '')
{
    if (empty($global_url))
        $global_url = $_SERVER['REQUEST_URI'];
    $parse_url = parse_url($global_url);
    $url = '';
    if (isset($parse_url['scheme']))
        $url .= $parse_url['scheme'] . '://' . $parse_url['host'];
    $url .= $parse_url['path'];
    if (!isset($parse_url['query']) and !isset($data))
        return $url;
    parse_str($parse_url['query'], $query);
    if (isset($data)) {
        if (is_string($data))
            parse_str($data, $data);
        $query = array_merge($query, $data);
    }
    $url .= '?' . http_build_query($query);
    return $url;
}


/**
 * 抛出异常
 * @param array $par
 * @return void
 * */
function thrown($par)
{
    static $again;
    if (isset($again))
        return;
    $again = '';

    include('./view/error.html');
    die();
}

/**
 * 返回ajax数据
 * @param array $data
 * @return void
 */
function ajaxReturn($data)
{
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($data));
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = true)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 获取数据库连接pdo对象
 * @return PDO
 * */
function db()
{
    global $config;
    static $_pdo;
    try {
        if (!isset($_pdo)) {
            $_pdo = new PDO("mysql:host={$config['DB']['host']};dbname={$config['DB']['name']};charset={$config['DB']['char']}", $config['DB']['user'], $config['DB']['pass']);
            $_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $_pdo;
    } catch (Exception $E) {
        thrown(['content' => $E->getMessage(), 'title' => 'Exception']);
    }

}

/**
 * 加密明文
 * @param string $str
 * @return string
 */
function hashCode($str)
{
    return sha1(md5(md5($str, true)) . '123456789..');
}

if (!function_exists('randStr')) {

    /**
     * 随机产生字符串
     * @param int $length 产生随机数长度
     * @param bool $int 是否产生数字
     * @param bool $lowercase 是否小写
     * @param bool $capital 是否大写
     * @return string
     * */
    function randStr($length = 1, $int = true, $lowercase = true, $capital = true)
    {
        $randPar = array(
            'number' => '1234567890',
            'character' => 'qwertyuiopasdfghjklzxcvbnm'
        );
        $str = '';
        if ($int) {//数字
            $str .= $randPar['number'];
        }
        if ($lowercase) {//小写
            $str .= $randPar['character'];
        }
        if ($capital) {//大写
            $str .= strtoupper($randPar['character']);
        }
        $str = str_shuffle($str);
        return substr($str, 0, $length);
    }
}


if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}


/**
 * 将制定数组链接为指定字符串
 * 如 array(key=>val,key1=>val2) 结果为：key=val&key1=val2
 * @param string $str 链接字符串1
 * @param string $str1 链接字符串2
 * @param array $arr 拼接数组
 * @return string
 * */
function joins($arr, $str = '=', $str1 = '&')
{

    if (!is_array($arr))
        return false;
    $new_arr = array();

    foreach ($arr as $k => $v) {
        $new_arr[] = "{$k}{$str}{$v}";
    }
    return join($str1, $new_arr);
}

/**
 * 执行xhell命令函数
 * @param string $shell 执行命令
 * @return mixed
 * */
function nexec($shell)
{
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    $process = proc_open($shell, $descriptorspec, $pipes);
    if (!$process)
        return false;
    $result = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[0]);
    $return_value = proc_close($process);

    return $return_value ? $error : $result;
}

/**
 * 网站路径转文件路径 只能用于tp
 * @param string $path
 * @param string $find
 * @return bool|string
 */
function siteFromFile($path, $find = '/')
{
    return substr($path, strlen($find));
}

/**
 * 秒数格式化、换算
 * @param integer $seconds
 * @return array
 */
function formatSeconds($seconds)
{
    $time = floor($seconds);
    $value = array(
        "years" => 0,
        "months" => 0,
        "days" => 0,
        "hours" => 0,
        "minutes" => 0,
        "seconds" => 0,
    );
    if ($time >= 31556926) {
        $value["years"] = floor($time / 31556926);
        $time = ($time % 31556926);
    }
    if ($time >= (30 * 86400)) {
        $value["months"] = floor($time / (30 * 86400));
        $time = ($time % (30 * 86400));
    }

    if ($time >= 86400) {
        $value["days"] = floor($time / 86400);
        $time = ($time % 86400);
    }
    if ($time >= 3600) {
        $value["hours"] = floor($time / 3600);
        $time = ($time % 3600);
    }
    if ($time >= 60) {
        $value["minutes"] = floor($time / 60);
        $time = ($time % 60);
    }
    $value["seconds"] = floor($time);
    return $value;
}

/**
 * 是否手机端
 * */
function isMobile()
{
    $ua = $_SERVER['HTTP_USER_AGENT'];
    return (strpos($ua, 'Mobile') !== false);
}

/**
 * 是否是管理员
 * @param $user_id
 * @return bool
 */
function isManage($user_id)
{
    global $config;
    return (in_array($user_id, $config['MANAGE_USER']));
}

/**
 * 计算月年 加减
 * @param $year
 * @param $toMon
 * @param $reckonNum
 * @return string
 */
function reckonMonthYear($reckonNum, $toMon = null, $year = null)
{
    if (!isset($year)) {
        $year = date('Y');
    }
    if (!$toMon) {
        $toMon = date('m');
    }
    $res = $toMon + $reckonNum;
    $year += (!$res ? -1 : floor($res / 12));

    $res = $res % 12;
    if ($res < 1) {
        $res += 12;
    } else {
        if ($res > 12) {
            $res -= 12;
        }
    }
    return $year . '-' . str_pad($res, 2, '0', STR_PAD_LEFT);
}


function fullescape($in)
{
    $out = '';
    for ($i = 0; $i < strlen($in); $i++) {
        $hex = dechex(ord($in[$i]));
        if ($hex == '')
            $out = $out . urlencode($in[$i]);
        else
            $out = $out . '%' . ((strlen($hex) == 1) ? ('0' . strtoupper($hex)) : (strtoupper($hex)));
    }
    $out = str_replace('+', '%20', $out);
    $out = str_replace('_', '%5F', $out);
    $out = str_replace('.', '%2E', $out);
    $out = str_replace('-', '%2D', $out);
    return $out;
}