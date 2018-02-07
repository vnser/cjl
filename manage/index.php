<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/10/19 0019
 * Time: 下午 15:52
 */
require_once '../comm.php';
require_once 'check_login.php';

if($_GET['do'] == 'get_online_user'){
    //在线用户
    $redis = \Lib\Redis::Instance();
    $onlineK = $redis->keys('vjl_online_*');
    $online = [];
    foreach ($onlineK as $k=>$v){
        $online[$k] = $redis->get($v);

        $online[$k]['qq'] = db()->query("select qq from vj_user  where user_id='{$online[$k]['user_id']}'")->fetchColumn(0);
        $online[$k]['video_tle'] = '';
        if($online[$k]['vid']){
            $online[$k]['video_tle'] = db()->query("select title from vj_video where id='{$online[$k]['vid']}'")->fetchColumn(0);
        }

    }
    ajaxReturn($online);
}

$title=  '管理首页';
$cdk_count = db()->query("select count(*) `count` from vj_cdk")->fetchColumn(0);
$cdk_n_count = db()->query("select count(*) `count` from vj_cdk where use_id=0")->fetchColumn(0);
$proportion  =  floor(($cdk_n_count/$cdk_count)*100);
$video = db()->query("select title,video_time,play_num from vj_video v join vj_video_coll c on c.video_id=v.video_id order by play_num desc limit 8")->fetchAll(2);
//print_r($video);

$user_last_login = db()->query("SELECT client_ip,u.qq,v.ua,v.add_time,v.user_id from vj_ulogin_record v join vj_user u on u.user_id=v.user_id/* GROUP by v.user_id*/ ORDER BY v.record_id desc limit 6")->fetchAll(2);
//print_r($user_last_login);
//今日会员登录
$user_login_count = db()->query("select count(*) from (select * from vj_ulogin_record where DATE_FORMAT(add_time,'%Y%m%d')=DATE_FORMAT(now(),'%Y%m%d') GROUP by user_id) a")->fetchColumn();
//注册会员数量
$reg_count = db()->query("select count(*) from vj_user where last_login_time<>'0000-00-00 00:00:00'")->fetchColumn();
//近七天使用卡密数量
$_7day = (24*3600*7);
$use_7cdk_count = db()->query("select count(*) from vj_cdk where unix_timestamp(use_time) >= unix_timestamp(DATE_FORMAT(now(),'%Y-%m-%d 00:00:00'))-(24*3600*7)")->fetchColumn();
//今日video播放量
$today_pay_num = db()->query("select pay_num from vj_count where DATE_FORMAT(add_time,'%Y%m%d')=DATE_FORMAT(now(),'%Y%m%d')")->fetchColumn();
//视频统计
$video_count = db()->query("select count(*) from vj_video ")->fetchColumn();
require_once 'view/index.html';
