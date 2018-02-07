<?php
include_once('./comm.php');
require_once 'checklogin.php';
use Lib\Redis;
/*session_start();
$user = $_SESSION['cjl_user'];*/

/*$user_sid = addslashes($_COOKIE['user_sid']);
$user = db()->query( "SELECT * FROM vj_user where sid='{$user_sid}' LIMIT 1")->fetch(2);
if(!$user){*/
   /* ajaxReturn([
        'status'=>false,
        'code'=>'Unauthorized access to!'
    ]);*/
/*    setcookie('user_sid',null,-1,'/');
//unset($_SESSION['cjl_user']);
    ajaxReturn([
        'status'=>false,
        'code'=>'账号在其他设备登录,您被强迫下线!'
    ]);*/
//}

if(!$login_flag){

    setcookie('user_sid',null,-1,'/');
    ajaxReturn([
        'status'=>false,
        'code'=>'账号登陆信息失效!(可能原因: 在其他地方登陆登等)'
    ]);
}
if($user['status']){
    setcookie('user_sid',null,-1,'/');
    ajaxReturn([
        'status'=>false,
        'code'=>'账号已被管理员锁定!'
    ]);
}
$online_id = $_COOKIE['online_id'];
if(!$online_id){
    $online_id = md5(uniqid());
    setcookie('online_id',$online_id,0,'/');
}
$redis = Redis::Instance();
$key = "vjl_online_{$online_id}";
/*if(isset($_GET['do'])){*/
    $odata = $redis->get($key);
    if(!$odata){
        $odata = [
            'user_id'=>$user['user_id'],
            'action'=>'',
            'vid'=>'',
            'browser_ua'=>$_SERVER['HTTP_USER_AGENT']
        ];
    }

//    if($_GET['action']){
        $odata['action'] = $_GET['action'];
//    }
//    if($_GET['vid']){
        $odata['vid'] = $_GET['vid'];
//    }
    $redis->set($key,$odata,7);
//    print_r($keys);
//    $con = ob_get_clean();
/*}*/

ajaxReturn([
    'status'=>true,
    'code'=>'success',
]);