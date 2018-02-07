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
    db()->exec('update vj_video_coll set down_status=-1,down_msg=\'\'  where coll_id=\'' . (float)$_GET['id'] . '\' limit 1');
    exit('<script>location.replace("' . $_SERVER['HTTP_REFERER'] . '");</script>');
}
if($do == 'reload'){
    db()->exec('update vj_video_coll set down_status=0,down_msg=\'\',create_task_time=unix_timestamp()  where coll_id=\'' . (float)$_GET['id'] . '\' limit 1');
    exit('<script>location.replace("' . $_SERVER['HTTP_REFERER'] . '");</script>');
}

$where = [];
$orders = 'create_task_time desc';
$status = $_GET['status'];
$search = $_GET['search'];
$order = $_GET['order'];
if (!empty($status) or $status === '0') {
    $where[] = "down_status='{$status}'";
}else{
    $where[] = 'down_status<>-1';
}
if (!empty($search)) {
    $where[] = "(v.id='$search' or c.video_id='{$search}')";
}
if(!empty($order) or $order === '0'){
    $orders = 'create_task_time ';
    if($order == 1){
        $orders .= 'asc';
    }else{
        $orders .= 'desc';
    }

}

$title = '下载任务管理';
$where = $where ? " where " . join(' and ', $where) : '';
$orders = $orders ? 'ORDER BY '.$orders:'';
$user_list_count = db()->query("select count(*) from vj_video_coll c LEFT join vj_video v USING(video_id) $where ")->fetchColumn(0);
$page = new \Lib\Page($user_list_count, 12);
$download = db()->query($sql = "select * from vj_video_coll c LEFT join vj_video v USING(video_id) $where $orders limit {$page->first_count},{$page->list_count}")->fetchAll(2);

include_once('view/download.html');