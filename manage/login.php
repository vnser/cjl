<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/12/1 0001
 * Time: 下午 17:21
 */
include_once '../comm.php';
if($_POST){
    $pwd = $_POST['pwd'];
    $user = $_POST['user'];
    if(md5($pwd) != md5($config['ADMIN_USER'][1]) or md5($user) != md5($config['ADMIN_USER'][0])){
        ajaxReturn([
            'status'=>false,
            'code'=>'登录失败,账号密码有误!'
        ]);
    }
    setcookie('admin_sid',md5(join($config['ADMIN_USER'])),time()+((3600*24)*7),'/');
    ajaxReturn(['status'=>true]);
}
//header('HTTP/1.1 404 Not Found');
 include_once ('view/login.html');