<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/12/26 0026
 * Time: 下午 20:17
 */

require_once '../comm.php';
/**
 * 响应数据
 * @param $message
 * @param bool $status
 * @param array $data
 */
function responseJson($message,$status = false,$data = []){
    header('Content-type: application/json');
    exit(json_encode([
        'message'=>$message,
        'status'=>$status,
        'data'=>$data
    ]));
}
$qq = $_GET['qq'];
$ckey = 'qqinfo_'.md5($qq);
$redis = \Lib\Redis::Instance();
$json = $redis->get($ckey);
if(!$json) {
    $api = 'http://r.pengyou.com/fcg-bin/cgi_get_portrait.fcg?uins=' . $qq;
    $req_h = array(
        'http' => array(
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
            'request_fulluri' => true,
            'proxy' => 'tcp://'.$config['HTTP_PROXY']
        )
    );
    stream_context_set_default($req_h);
    $con = @file_get_contents($api) or responseJson($php_errormsg);
    if ($con) {
        $con = mb_convert_encoding($con, 'utf-8', 'gbk');
    }
    preg_match('/portraitCallBack\(([\s\S]*)\)/', $con, $mch) or responseJson('内容抓取失败');
    $data = json_decode($mch[1], true);
    $json = [];
    foreach ($data as $k => $v) {
        $json[$k] = [
            'headimgurl' => $v[0],
            'nickname' => $v[6],
            'qq' => $k
        ];
    }
    $redis->set($ckey,$json,7200);
}
responseJson('success',true,$json);