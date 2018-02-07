<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 16-7-23
 * Time: 上午5:24
 */
include_once('comm.php');
include_once('checklogin.php');
if ($login_flag) {
    ajaxReturn([
        'status' => false,
        'code' => '你已登录,请刷新页面!'
    ]);
}
//session_start();
$id = addslashes($_POST['id']);
$pass = hashCode(addslashes($_POST['pwd']));
$stmt = db()->prepare("SELECT * FROM vj_user where (user_id= :username or qq= :username) and pwd= :pwd LIMIT 1");
$stmt->bindValue(':username', $id);
$stmt->bindValue(':pwd', $pass);
$stmt->execute();
$user = $stmt->fetch(2);
if (!$user) {
    ajaxReturn([
        'status' => false,
        'code' => '登录失败,账号或密码有误!'
    ]);
}
/*if(strtotime($user['expire_time']) <= time() ){
    ajaxReturn([
        'status'=>false,
        'code'=>'账户已到期,请先充值后'
    ])
}*/
$sid = hashCode(uniqid() . $user['user_id'] . $pass);
db()->exec("UPDATE vj_user SET  sid='$sid',last_login_time=now() WHERE user_id='{$user['user_id']}' limit 1");
$todayLoginCount = db()->query("select count(*) login_count from vj_ulogin_record WHERE unix_timestamp(add_time) >= unix_timestamp('" . date('Y-m-d 00:00:00') . "') and user_id='{$user['user_id']}'")->fetchColumn();
if (!isManage($user['user_id'])) {
    if ($todayLoginCount >= $config['LIMIT_LOGIN']) {
        ajaxReturn([
            'status' => false,
            'code' => '登录失败,该账号今日登录次数过多已被限制登录,请稍后再试!'
        ]);
    }
}
preg_match('/\(([\s\S]*?)\)/', $_SERVER['HTTP_USER_AGENT'], $mch);
$ua = $mch[1];
db()->exec("INSERT INTO vj_ulogin_record(`ua`,`client_ip`,`user_id`) VALUE ('{$ua}','" . $_SERVER['HTTP_CF_CONNECTING_IP'] . "','{$user['user_id']}')");
setcookie('user_sid', $sid, time() + (3600 * 24 * 5), '/');
$redis = \Lib\Redis::Instance();
$redis->set("user_aging_{$sid}", [
//    'ip' => $_SERVER['HTTP_CF_CONNECTING_IP'],
    'user_id' => $user['user_id'],
    'ua'=>$_SERVER['HTTP_USER_AGENT']
], time() + (3600 * 24 * 5));
ajaxReturn([
    'status' => true,
    'code' => '登录成功'
]);