<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/10/19 0019
 * Time: 下午 15:22
 */

require_once 'comm.php';
require_once 'checklogin.php';
if(!$login_flag){
    ajaxReturn([
        'status'=>false,
        'code'=>'请先登录!'
    ]);
}
if($_GET['do'] == 'bind_qq'){
    session_start();
    if(!$_SESSION['ver']){
        ajaxReturn([
            'status'=>false,
            'code'=>'请先获取验证码!'
        ]);
    }
    $qq = $_POST['qq'];
    $authCode = $_POST['validation'];
    if($_SESSION['ver']['qq'] != $qq){
        ajaxReturn([
            'status'=>false,
            'code'=>'请输入获取验证码时的QQ号!'
        ]);
    }
    if($_SESSION['ver']['code'] != $authCode){
        ajaxReturn([
            'status'=>false,
            'code'=>'输入的验证码不正确!'
        ]);
    }
    $res = db()->query("select * from vj_user where qq='{$qq}' ")->fetch(2);
    if($res){
        ajaxReturn([
            'status'=>false,
            'code'=>'输入的QQ号已经绑定过其他账号,请更换QQ号!'
        ]);
    }
    db()->exec("update vj_user set qq='{$qq}' where user_id='{$user['user_id']}'");
    ajaxReturn([
        'status'=>true,
        'code'=>'绑定成功(下次登录可以绑定的QQ号登录)!'
    ]);
}
if($_GET['do'] == 'out_login'){
    setcookie('user_sid',null,-1,'/');
    db()->exec("UPDATE vj_user set sid='".hashCode(uniqid().$user['user_id'])."' where user_id='{$user['user_id']}'");
    $redis->rm("user_aging_{$user_sid}");
    ajaxReturn([
        'status'=>true,
        'code'=>'ok,注销登录成功!'
    ]);
}
$o_pwd = addslashes($_POST['o_pwd']);
$n_pwd = addslashes($_POST['n1_pwd']);

$oh_pwd = hashCode($o_pwd);
if($oh_pwd !== $user['pwd']){
    ajaxReturn([
        'status'=>false,
        'code'=>'当前密码验证不正确!'
    ]);
}
if($o_pwd === $n_pwd){
    ajaxReturn([
        'status'=>false,
        'code'=>'新密码不能跟旧密码一致!'
    ]);
}
setcookie('user_sid',null,-1,'/');
db()->exec("UPDATE vj_user set pwd='".hashCode($n_pwd)."',sid='".hashCode(uniqid().$user['user_id'].$n_pwd)."' where user_id='{$user['user_id']}'");
ajaxReturn([
    'status'=>true,
    'code'=>'修改成功!'
]);