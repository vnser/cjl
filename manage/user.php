<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/11/24 0024
 * Time: 下午 17:45
 */
require_once '../comm.php';
require_once 'check_login.php';
$do = $_GET['do'];
if ($do == 'del') {
    db()->exec('delete from vj_user where user_id=\'' . $_GET['id'] . '\' limit 1');
    exit('<script>location.replace("' . $_SERVER['HTTP_REFERER'] . '");</script>');
}
if ($do == 'modify') {
    $pwd = hashCode($_POST['pwd']);
    $sid = hashCode($pwd);
    db()->exec("update vj_user set pwd='{$pwd}',sid='{$sid}' where user_id='{$_POST['user_id']}' limit 1");
    ajaxReturn([
        'status' => true,
        'code' => '修改成功!'
    ]);
}
if ($do == 'lock') {
    db()->exec("update vj_user set status=1 where user_id='{$_GET['user_id']}' limit 1");
    exit('<script>location.replace("' . $_SERVER['HTTP_REFERER'] . '");</script>');
}

if ($do == 'unlock') {
    db()->exec("update vj_user set status=0 where user_id='{$_GET['user_id']}' limit 1");
    exit('<script>location.replace("' . $_SERVER['HTTP_REFERER'] . '");</script>');
}
if($do == 'get_classify'){
    $classify_list = db()->query("select `classify`,count(*) `count` from vj_user GROUP by `classify`")->fetchAll(2);
    ajaxReturn($classify_list);
}
if($do == 'create_user'){
    if($_POST){
        $userStr = '';
        for ($i=0;$i<$_POST['num'];$i++){
            $pwd = randStr(8);
            $hpwd = hashCode($pwd);
            $sid = hashCode($hpwd);
            $expire_time = date('Y-m-d H:i:s');
            db()->exec("INSERT INTO vj_user(pwd,sid,expire_time,classify) VALUE ('$hpwd','$sid','$expire_time','{$_POST['classify']}')");
            $userStr .= db()->lastInsertId().' '.$pwd."\r\n";
        }
        $key = md5(uniqid());
        session_start();
        $_SESSION['user'][$key] = $userStr;
        ajaxReturn([
            'status'=>true,
            'code'=>'生成成功('.$_POST['num'].')个会员!',
            'key'=>$key
        ]);
    }
    include('view/create_user.html');
    exit;
}
if($do == 'down_new_user'){
    session_start();
    $code = $_SESSION['user'][$_GET['key']];
    header('Content-Type: application/octet-stream');//告诉浏览器输出内容类型，必须
    header('Content-Disposition: attachment; filename="user_'.md5(uniqid()).'.txt"');
    exit($code);
}

$where = [];
$status = $_GET['status'];
$search = $_GET['search'];
$classify = $_GET['classify'];
$is_bind_qq = $_GET['is_bind_qq'];
if (!empty($status) or $status === '0') {
    $where[] = "status='{$status}'";
}
if (!empty($search)) {
    $where[] = "user_id='$search' or qq='{$search}'";
}
if(!empty($is_bind_qq) or $is_bind_qq === '0'){
    $where[] = "`qq`".($is_bind_qq?"!":'')."=''";
}
if (!empty($classify)){
    $where[] = "classify='{$classify}'";
}
$title = '会员管理';
$where = $where ? " where " . join(' and ', $where) : '';
$user_list_count = db()->query("select count(*) from vj_user $where ")->fetchColumn(0);
$page = new \Lib\Page($user_list_count, 10);
$user_list = db()->query("select * from vj_user $where limit {$page->first_count},{$page->list_count}")->fetchAll(2);

include_once('view/user.html');