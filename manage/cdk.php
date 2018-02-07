<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/11/24 0024
 * Time: 下午 14:18
 */
require_once '../comm.php';
require_once 'check_login.php';
$do = $_GET['do'];
if($do == 'del'){
    db()->exec("delete from vj_cdk where cdk_id='{$_GET['id']}'");
    exit('<script>location.replace("'.$_SERVER['HTTP_REFERER'].'");</script>');
}
if($do == 'get_face'){
    $face_list = db()->query("select time,round(`time`/(3600*24),1) day,count(*)face_count from vj_cdk GROUP by time")->fetchAll(2);
    ajaxReturn($face_list);
}
if($do == 'get_classify'){
    $classify_list = db()->query("select `classify`,count(*) `count` from vj_cdk GROUP by `classify`")->fetchAll(2);
    ajaxReturn($classify_list);
}
if($do == 'create_cdk'){
    if($_POST){
        $time = ($_POST['day']*3600*24);
        session_start();
        $key = md5(uniqid());
        for ($i=0;$i<$_POST['num'];$i++){
            $code = randStr(18,true,false);
            $_SESSION['cdk'][$key][] = $code;
            db()->exec("INSERT INTO vj_cdk(code,`time`,classify) VALUE ('$code','$time','{$_POST['classify']}')");
        }
        ajaxReturn([
            'status'=>true,
            'code'=>"成功生成({$_POST['num']})个卡密!",
            'key'=>$key
        ]);
        exit;
    }
    $title = '创建卡密';

    include_once 'view/create_cdk.html';
    exit;
}
if($do == 'down_new_cdk'){
    session_start();
    $code = $_SESSION['cdk'][$_GET['key']];
    header('Content-Type: application/octet-stream');//告诉浏览器输出内容类型，必须
    header('Content-Disposition: attachment; filename="cdk_'.md5(uniqid()).'.txt"');
    exit(join("\r\n",$code));
}
$title = '卡密管理';
$where = [];
$status = $_GET['status'];
$search = $_GET['search'];
$time = $_GET['face_val'];
$classify = $_GET['classify'];
$start_time = $_GET['start_time'];
$end_time = $_GET['end_time'];
if(!empty($start_time)){
    $where[] = "add_time >= '{$start_time}'";
}
if(!empty($end_time)){
    $where[] = "add_time <= '{$end_time}'";
}
if(!empty($status) or $status === '0'){
    $where[]= "use_id".($status?'!=':'=').'0';
}
if(!empty($search)){
    if(is_numeric($search)){
        $where[]= " use_id='{$search}'";
    }else{
        $where[]= " code='$search'";
    }
}
if(!empty($time) or $time==='0'){
    $where[] = "time='{$time}'";
}
if (!empty($classify)){
    $where[] = "classify='{$classify}'";
}
$where = $where?" where ".join(' and ',$where):'';

if($do=='out_cdk'){
    header('Content-Type: application/octet-stream');//告诉浏览器输出内容类型，必须
    header('Content-Disposition: attachment; filename="cdk_'.md5($where).'.txt"');
    $cdk_list = db()->query("select code from vj_cdk $where")->fetchAll(2);
    foreach ($cdk_list as $v){
        echo "{$v['code']}\r\n";
    }
    exit;
}

$cdk_list_count = db()->query("select count(*) `count` from vj_cdk $where ")->fetchColumn(0);
$page = new \Lib\Page($cdk_list_count,15);
$cdk_list = db()->query("select * from vj_cdk $where LIMIT  {$page->first_count},{$page->list_count}")->fetchAll(2);
include('view/cdk.html');
