<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/10/19 0019
 * Time: 下午 14:01
 */

require_once 'comm.php';
require_once 'checklogin.php';
//print_r($_SERVER);
if (!$login_flag) {
    ajaxReturn([
        'status' => false,
        'code' => '请先登录!'
    ]);
}
if (strtotime($user['expire_time']) <= time()) {
    ajaxReturn([
        'status' => false,
        'code' => '账户已到期,请先充值后再播放！'
    ]);
}
if ($user['status']) {
    //setcookie('user_sid',null,-1,'/');
    ajaxReturn([
        'status' => false,
        'code' => '账号已被管理员锁定!'
    ]);
}
if (empty($user['qq'])) {
    ajaxReturn([
        'status' => false,
        'code' => '请先绑定QQ后在进行观看!'
    ]);
}
if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'){
    ajaxReturn([
        'status' => false,
        'code' => 'error'
    ]);
}
$video_id = (float)$_POST['video_id'];
if ($_GET['do'] == 'url') {

    $result = db()->query("select c.down_status,c.is_local_del,v.local,c.play_num,s.is_down,v.local,v.video_id,s.use_video,v.id,video_url,video_host,scheme from vj_video v join vj_type t on t.id=v.type_id join vj_server s on s.id=t.server_id join vj_video_coll c USING(video_id) where v.id='{$video_id}'")->fetch(PDO::FETCH_ASSOC);

    $video_url = '';
//    var_dump($config);
    if ($result['local']) {
        $video_url .= "http://" . $config['VIDEO_DOMAIN'] . $result['video_url'];
    } else {
        if ($result['use_video']) {
            $video_url = $result['scheme'] . $result['video_host'] . $result['video_url'];
        } else {
            $video_url = $result['video_url'];
        }
    }
    $result['play_num']++;
    $modifySql = "play_num=play_num+1";
    if ($result['play_num'] >= 10) {
        if (!$result['is_local_del'] and !$result['local'] and $result['is_down'] and $result['down_status'] == '-1') {
            $modifySql .= ",down_status=0,create_task_time=unix_timestamp()";
        }
    }
    db()->exec("update vj_video_coll set {$modifySql} where video_id='{$result['video_id']}'");

    //增加当天播放量
    $countWhere = "DATE_FORMAT(add_time,'%Y%m%d')=DATE_FORMAT(now(),'%Y%m%d')";
    $countRes = db()->query("select * from vj_count where $countWhere limit 1")->fetch(2);
    if(!$countRes){
        //增加统计
        db()->exec('insert into vj_count value(null,0,null)');
    }
    db()->exec("update vj_count set pay_num=pay_num+1 where $countWhere limit 1");
    ajaxReturn([
        'status' => true,
        'url' => $video_url
    ]);
}


if ($_GET['do'] == 'set_time') {
    $seconds = $_POST['seconds'];
    if ($seconds < 1) {
        ajaxReturn([
            'status' => false,
            'code' => 'error'
        ]);
    }
    $timeArr = formatSeconds($seconds);
    $time = '';
    if ($timeArr['hours'] > 1) {
        $timeArr['hours'] = ($timeArr['hours'] < 10 ? str_pad($timeArr['hours'], 2, '0', STR_PAD_LEFT) : $timeArr['hours']);
        $time = "{$timeArr['hours']}:";
    }
    $timeArr['minutes'] = $timeArr['minutes'] ? ($timeArr['minutes'] < 10 ? str_pad($timeArr['minutes'], 2, '0', STR_PAD_LEFT) : $timeArr['minutes']) : '00';
    $timeArr['seconds'] = $timeArr['seconds'] ? ($timeArr['seconds'] < 10 ? str_pad($timeArr['seconds'], 2, '0', STR_PAD_LEFT) : $timeArr['seconds']) : '00';
    $time .= "{$timeArr['minutes']}:{$timeArr['seconds']}";
    db()->exec("update vj_video set video_time='{$time}' where id='{$video_id}' limit 1");
    ajaxReturn([
        'status' => true,
        'code' => 'success',
        'time' => $time
    ]);
}

if (!isManage($user['user_id'])) {
    ajaxReturn([
        'status' => false,
        'code' => '账号没有权限!'
    ]);
}


$video_id = (float)$_POST['video_id'];
$video = db()->query("select c.digest,c.down_status,s.is_down,`local`,video_url,video_img,video_id,is_local_del from vj_video v join vj_type t on t.id=v.type_id join vj_server s on s.id=t.server_id join vj_video_coll c USING(video_id) where v.id='{$video_id}' limit 1")->fetch(2);
if (!$video) {
    ajaxReturn([
        'status' => false,
        'code' => '视频不存在或已删除!'
    ]);
}
if ($_GET['do'] == 'digest'){
    $digest = ($video['digest'] ? 0 : 1);
    db()->exec("update vj_video_coll set `digest`='{$digest}' where video_id='{$video['video_id']}' limit 1");
    ajaxReturn([
        'status' => true,
        'code' => '操作成功!'
    ]);
}


if ($_GET['do'] == 'del') {
    if ($video['local']) {
        //删除文件信息
        @unlink(siteFromFile($video['video_url']));
        @unlink(siteFromFile($video['video_img']));
        db()->exec("update vj_video_coll set `is_local_del`=1 where video_id='{$video['video_id']}' limit 1");
    }
    db()->exec("delete from vj_video where id='{$video_id}'");
    ajaxReturn([
        'status' => true,
        'code' => 'success'
    ]);

}

if ($_GET['do'] == 'create_download_task') {
    if (!$video['is_down']) {
        ajaxReturn([
            'status' => false,
            'code' => '该服务器的视频禁止下载!'
        ]);
    }
    if ($video['local']) {
        ajaxReturn([
            'status' => false,
            'code' => '视频已经是本地文件,无须创建下载任务!'
        ]);
    }
    if ($video['is_local_del']) {
        ajaxReturn([
            'status' => false,
            'code' => '视频下载到本地已被删除,禁止再次添加此视频任务!'
        ]);
    }
    if ($video['down_status'] != '-1') {
        ajaxReturn([
            'status' => false,
            'code' => '该视频已经添加过任务,禁止再次添加此视频任务!'
        ]);
    }
    db()->exec("update vj_video_coll set down_status=0,create_task_time=unix_timestamp() where video_id='{$video['video_id']}' limit 1");
    ajaxReturn([
        'status' => true,
        'code' => 'create video task success!'
    ]);
}