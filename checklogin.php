<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-7-23
 * Time: 上午5:37
 */
//session_start();
$login_flag = true;
/*if(/9!$config['IS_LOGIN_VNSER'])
    return false;*/
$user_sid = addslashes($_COOKIE['user_sid']);
$user = db()->query( "SELECT * FROM vj_user where sid='{$user_sid}' LIMIT 1")->fetch(2);
$redis = \Lib\Redis::Instance();
$user_aging_key = "user_aging_{$user_sid}";
if(!$user){
    $redis->rm($user_aging_key);
    $login_flag = false;
    return false;
}

$aging = $redis->get($user_aging_key);
if (!$aging){
    $login_flag = false;
    return false;
}
/*if($aging['ip'] != $_SERVER['HTTP_CF_CONNECTING_IP']){
    //ip限制
    $redis->rm($user_aging_key);
    $login_flag = false;
    return false;
}*/
if(md5($aging['ua']) != md5($_SERVER['HTTP_USER_AGENT']) ){
    $redis->rm($user_aging_key);
    $login_flag = false;
    return false;
}