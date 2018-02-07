<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/10/19 0019
 * Time: 下午 14:52
 */
require_once 'comm.php';
require_once 'checklogin.php';
if(!$login_flag){
    ajaxReturn([
        'status'=>false,
        'code'=>'请先登录!'
    ]);
}
$rechar = db()->query("select * from vj_cdk where use_id = '{$user['user_id']}' and unix_timestamp(use_time)+7200 >= unix_timestamp() limit 1")->fetch(2);
if($rechar){
    ajaxReturn([
        'status'=>false,
        'code'=>'2个小时限制充值1次!'
    ]);
}
$cdk = addslashes($_POST['cdk']);
$res = db()->query("SELECT * FROM vj_cdk where code='{$cdk}' and use_id=0 limit 1")->fetch(2);
if(!$res){
    ajaxReturn([
        'status'=>false,
        'code'=>'卡密已被使用或有误!'
    ]);
}
db()->exec("UPDATE vj_user SET expire_time=FROM_UNIXTIME(unix_timestamp(if(expire_time<=now(),now(),expire_time))+{$res['time']}) where user_id='{$user['user_id']}'");
db()->exec("UPDATE vj_cdk set use_id='{$user['user_id']}',use_time=now() where code='{$cdk}'");
ajaxReturn([
    'status'=>true,
    'code'=>'充值成功!'
]);